<?php

namespace DLTools\Database;

use DLTools\Config\Credentials;
use DLTools\Config\DLConfig;
use Error;
use Exception;
use PDO;

/**
 * Esta clase se está reescribiendo...
 */

/**
 * Permite el acceso a la base de datos definidas en las variables
 * de entorno.
 * 
 * @package DLTools\Database
 * 
 * @author David E Luna M <davidlunamonilla@gmail.com>
 * @license MIT
 * @version v1.0.0 (2022-06-01) - Initial release
 */

class DLDatabase {

    use DLConfig;
    use DLQueryBuilder;

    /**
     * Operador lógico
     * 
     * @var string AND
     */
    public const AND = "AND";

    /**
     * Operador lógico
     * 
     * @var string OR
     */
    public const OR = "OR";

    private static ?self $instance = NULL;

    /**
     * Establece los campos a seleccionar en una consulta SQL.
     *
     * Configura los campos que se incluirán en la cláusula `SELECT` de una consulta SQL.
     * Si no se proporcionan campos específicos, se mantiene el valor por defecto "*".
     * 
     * También se encarga de limpiar los espacios en blanco y eliminar duplicados.
     *
     * @param string ...$fields Lista de nombres de columnas a seleccionar en la consulta SQL.
     * @return self Instancia actual del objeto para permitir encadenamiento de métodos.
     */
    public function select(string ...$fields): self {

        if (empty($fields)) {
            return $this;
        }

        if ($this->fields == "*") {
            $this->fields = implode(", ", $fields);
        }

        if (!$this->empty($this->fields) && $this->fields != "*") {
            $this->fields .= ", " . implode(", ", $fields);
        }

        $this->fields = trim($this->fields);
        $this->fields = preg_replace("/\s+/", ' ', $this->fields);

        if ($this->empty($this->fields)) {
            $this->fields = "*";
        }

        if ($this->fields != "*") {
            $this->fields = $this->get_unique_field($this->fields);
        }

        return $this;
    }

    /**
     * Obtiene la lista de todas las tablas de la base de datos.
     *
     * Este método recupera la lista de tablas de la base de datos en función del 
     * motor de base de datos utilizado. Soporta MySQL, MariaDB, PostgreSQL y SQLite.
     *
     * @param int $page Número de página para la paginación de los resultados.
     * @param int $rows Número de filas por página.
     * 
     * @return array Listado paginado de las tablas de la base de datos.
     *
     * @throws DatabaseException Si ocurre un error en la consulta.
     */
    public static function show_tables(int $page = 1, int $rows = 200): array {
        /** @var DLDatabase $db Instancia de la base de datos. */
        $db = self::get_instance();

        $db->set_show_tables();

        /** @var Credentials $credentials Credenciales del sistema. */
        $credentials = $db->get_credentials();

        /** @var string $drive Motor de la base de datos en uso. */
        $drive = $credentials->get_drive();

        /** @var string $query Consulta SQL para obtener las tablas según el motor de base de datos. */
        $query = match ($drive) {
            "mysql", "mariadb" => "SELECT * FROM information_schema.tables WHERE table_schema = DATABASE()",
            "pgsql" => "SELECT * FROM pg_catalog.pg_tables WHERE schemaname NOT IN ('pg_catalog', 'information_schema')",
            "sqlite" => "SELECT * FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
        };

        /** @var array $data Resultado de la consulta con paginación aplicada. */
        $data = $db->query($query)
            // ->get();
            ->paginate($page, $rows);

        $db->set_show_tables(show: false);

        return $data;
    }


    /**
     * Genera una lista única de nombres de columnas a partir de una cadena de entrada.
     *
     * Esta función toma una cadena de nombres de columnas separadas por comas,
     * elimina espacios en blanco innecesarios y devuelve una lista única de nombres de columnas.
     *
     * @param string $fields Lista de nombres de columnas separados por comas.
     * @return string Lista única de nombres de columnas, separados por comas.
     */
    private function get_unique_field(string $fields): string {

        /** @var array<string,string> $columns Array asociativo para almacenar nombres únicos de columnas. */
        $columns = [];

        /** @var string[] $parts Lista de nombres de columnas después de dividir la cadena de entrada. */
        $parts = explode(",", $fields);

        foreach ($parts as $part) {
            if (!is_string($part)) continue;

            $part = trim($part);
            if (empty($part)) continue;

            $columns[$part] = $part;
        }

        return implode(", ", $columns);
    }


    /**
     * Actualiza los registros de una tabla
     *
     * @param boolean $test
     * @return string|bool
     */
    public function update(array $fields, bool $test = false): string | bool {
        $this->set_operation(self::UPDATE);

        /**
         * Indicador de si se ha completado el proceso de actualización.
         * 
         * @var boolean
         */
        $completed = false;

        if ($this->empty($fields)) {
            throw new Error("Especifique los campos a modificar\n<br>");
        }

        if ($this->empty($this->table)) {
            throw new Error("Debe seleccionar la tabla que desea modificar");
        }

        $this->set_options();

        $new_fields = [];
        $params = [];

        foreach ($fields as $field => $value) {
            $key = ":{$field}";


            if (array_key_exists($key, $this->param)) {
                array_push($new_fields, "{$field} = {$key}_v");
                $this->param["{$key}_v"] = $value;

                continue;
            }

            array_push($new_fields, "{$field} = {$key}");
            $this->param[$key] = $value;
        }

        $params = $this->param;
        $query = "UPDATE {$this->table} SET " . join(", ", $new_fields);

        if (!($this->empty($this->options))) {
            $query .= $this->options;
        }

        $this->clean();

        if ($test) {
            return $query;
        }

        $stmt = $this->pdo->prepare($query);

        $completed = $stmt->execute($params);

        return $completed;
    }

    /**
     * Elimina registros de una tabla
     *
     * @param boolean $test
     * @return string|bool
     */
    public function delete(bool $test = false): string | bool {
        $this->set_operation(self::DELETE);

        /**
         * Indica si el proceso se ha completado.
         * 
         * @var boolean
         */
        $completed = false;

        $query = $this->get_query();
        $param = $this->param;

        $this->clean();

        if ($test) {
            return $query;
        }

        $stmt = $this->pdo->prepare($query);
        $completed = $stmt->execute($param);

        return $completed;
    }

    /**
     * Establece la tabla de la consulta SQL.
     *
     * Este método define la tabla sobre la cual se ejecutará la consulta,
     * eliminando espacios en blanco adicionales en el nombre de la tabla.
     *
     * @param string $table Nombre de la tabla a utilizar en la consulta.
     * @return self Instancia actual del objeto para permitir encadenamiento de métodos.
     */
    public function from(string $table): self {
        $this->table = preg_replace("/\s+/i", ' ', $table);
        $this->table = trim($this->table);
        $this->table = "{$this->table}";

        return $this;
    }

    /**
     * Devuelve registros de una tabla
     *
     * @return array
     */
    public function get(array $param = []): array {

        /**
         * Datos de la consulta
         * 
         * @var array
         */
        $data = [];

        /** @var string $query */
        $query = $this->get_query();

        $this->param = [...$this->param, ...$param];

        if ($this->empty($query)) {
            throw new Error("La consulta no puede estar vacía", 500);
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($this->param);

        $this->clean();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * Obtiene el primer registro de la consulta SQL.
     *
     * Este método ejecuta la consulta SQL actual y devuelve el primer registro encontrado.
     * Si la consulta no está definida, intenta establecer una consulta `SELECT`.
     * En caso de que la consulta siga vacía, lanza un error.
     * 
     * @param array $param Parámetros adicionales para la consulta preparada.
     * @return array Primer registro obtenido de la base de datos o un array vacío si no hay resultados.
     * @throws Error Si la consulta SQL está vacía antes de ejecutarse.
     */
    public function first(array $param = []): array {
        $data = [];

        // Genera la consulta si no es personalizada.
        if (!$this->custom) {
            $this->get_query();
        }

        // Agrega parámetros adicionales a la consulta.
        $this->param = [...$this->param, ...$param];

        // Si la consulta está vacía, intenta establecer una consulta SELECT.
        if ($this->empty($this->query)) {
            $this->select();
        }

        // Si la consulta sigue vacía, lanza un error.
        if ($this->empty($this->query)) {
            throw new Error("La consulta SQL no puede estar vacía");
        }

        // Prepara y ejecuta la consulta.
        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->param);

        // Obtiene el primer registro como un array asociativo.
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Limpia el estado del objeto para futuras consultas.
        $this->clean();

        // Retorna el primer registro o un array vacío si no hay resultados.
        return $data !== false ? $data : [];
    }


    /**
     * Devuelve una consulta SQL construída a partir del constructor
     * de consulta de DLTools.
     *
     * @return string
     */
    public function get_query(): string {
        $this->load_table();

        if ($this->customized) {
            $this->set_options();

            $options = $this->options;
            $query = $this->query;
            $params = $this->param;

            $this->clean();

            $this->options = $options;
            $this->query = "{$query}{$this->options}";
            $this->param = $params;

            return $this->query;
        }

        $this->load_operation();

        /** @var string|null $operation */
        $operation = $this->get_operation();

        /**
         * @var string $query Sentencia SQL.
         */
        $query = "";

        $this->set_options();

        // Tipos de consultas elegidas:
        if ($this->empty($this->table)) {
            throw new Error("Debe seleccionar una tabla");
        }

        if ($this->empty($this->fields)) {
            $this->fields = "*";
        }

        if ($this->is_select()) {
            $query = "{$operation} {$this->fields} FROM {$this->table} {$this->options}";
        }

        if ($this->is_update()) {
            // echo PHP_EOL . $this->update;
            $query = $this->update . " {$this->table}";
        }

        if ($this->is_delete()) {
            $query = "{$operation} FROM {$this->table}{$this->options}";
        }

        if (!($this->empty($this->queryLast)) && !($this->empty($this->column))) {
            $query = "SELECT {$this->fields} FROM {$this->table} WHERE {$this->column} = (SELECT MAX({$this->column}) FROM {$this->table}) LIMIT 1";
        }

        if ($this->empty($query)) {
            $this->select();
        }

        $param = $this->param;
        $this->clean();

        $this->query = $query;
        $this->param = $param;

        return $query;
    }

    /**
     * Comprueba si una variable está vacía.
     * 
     * @param array | string $value
     * @return bool
     */
    private function empty(array | string $value): bool {
        if (is_string($value)) {
            return empty(trim($value));
        }

        return empty($value);
    }

    /**
     * Inserta registros en una tabla SQL.
     *
     * Este método permite insertar datos en una tabla de la base de datos. 
     * Puede manejar tanto una única inserción como inserciones múltiples en un solo proceso.
     * También admite un modo de prueba (`test`), en el cual solo devuelve la consulta generada sin ejecutarla.
     *
     * @param array $fields Arreglo asociativo con los nombres de los campos y sus respectivos valores.
     *                      Para múltiples inserciones, se debe proporcionar un arreglo de arreglos.
     * @param bool $test Indica si se ejecutará en modo de prueba (`true`) o en modo real (`false`).
     *                   Si es `true`, devuelve la consulta SQL generada en formato de cadena.
     *
     * @throws Error Si no se ha definido una tabla antes de ejecutar la inserción.
     *
     * @return string|bool Retorna `true` si la inserción se ejecuta con éxito, `false` si falla.
     *                     En modo de prueba, retorna la consulta SQL en formato de cadena.
     */
    public function insert(array $fields, bool $test = false): string|bool {
        $this->set_operation(self::INSERT);
        return $this->create($fields, $test);
    }

    /**
     * Inserta o reemplaza registros en una tabla SQL.
     * 
     * Utiliza la operación REPLACE, que inserta un nuevo registro o reemplaza 
     * uno existente si la clave primaria o índice único coincide.
     *
     * @param array $fields Datos a insertar o reemplazar.
     * @param bool $test Determina si se ejecuta en modo de prueba (true) o real (false).
     * @return string|bool Retorna la consulta SQL en modo prueba o `true`/`false` en ejecución real.
     * 
     * @throws Exception
     */
    public function replace(array $fields, bool $test = false): string|bool {
        $credentials = $this->get_credentials();

        /** @var string $drive */
        $drive = $credentials->get_drive();

        if (!in_array($drive, self::DRIVERS, true)) {
            throw new Exception("REPLACE INTO no es compatible con {$drive}. Solo está disponible para MySQL/MariaDB", 500);
        }

        $this->set_operation(self::REPLACE);
        return $this->create($fields, $test, self::REPLACE);
    }


    /**
     * Crea un nuevo registro en la tabla especificada.
     *
     * @param array $fields Campos y valores a insertar en la tabla.
     * @param bool $test Determina si la consulta se ejecuta en modo de prueba o realmente en la base de datos.
     * @param string $operation Tipo de operación SQL a realizar, por defecto es INSERT.
     * @return string|bool Devuelve la consulta SQL en modo de prueba o true/false dependiendo del éxito de la ejecución.
     *
     * @throws Error Si no se ha seleccionado una tabla válida.
     */
    private function create(array $fields, bool $test = false, string $operation = SELF::INSERT): string|bool {
        $table = $this->table;

        /** @var string $drive */
        $drive = $this->get_credentials();

        if ($this->empty($table)) {
            throw new Error("Seleccione una tabla");
        }

        $keys = [];
        $new_keys = [];
        $values = [];

        foreach ($fields as $key => $value) {
            if (is_string($key)) {
                array_push($keys, $this->get_field_quote($key));
                array_push($new_keys, ":$key");
                $values[":" . $key] = $value;
            }

            if (is_array($value) && is_numeric($key)) {
                $register = [];

                foreach ($value as $new_key => $new_value) {
                    if ($key === 0) {
                        array_push($keys, $this->get_field_quote($new_key));
                        array_push($new_keys, ":$new_key");
                    }

                    $register[":" . $new_key] = $new_value;
                }

                array_push($values, $register);
            }
        }

        $this->fields = join(", ", $keys);
        $this->values = $values;
        $this->new_keys = join(", ", $new_keys);

        $query = in_array($drive, self::DRIVERS)
            ? "{$operation} INTO `{$this->table}` ({$this->fields}) VALUES ({$this->new_keys})"
            : "{$operation} INTO {$this->table} ({$this->fields}) VALUES ({$this->new_keys})";

        if (!$test) {
            $stmt = $this->pdo->prepare($query);

            if (array_key_exists(0, $this->values)) {
                $this->pdo->beginTransaction();

                foreach ($this->values as $register) {
                    $stmt->execute($register);
                }

                $this->clean();
                return $this->pdo->commit();
            }

            $this->clean();
            return $stmt->execute($this->values);
        }

        if ($test) {
            $this->clean();
            return $query;
        }

        return false;
    }

    /**
     * Devuelve el nombre de un campo con las comillas adecuadas según el motor de base de datos.
     *
     * Para MySQL/MariaDB, usa backticks (` `).  
     * Para otros motores como PostgreSQL o SQLite, usa comillas dobles (" ").  
     *
     * @param string $field Nombre del campo a entrecomillar.
     * @return string Nombre del campo con las comillas correspondientes.
     */
    private function get_field_quote(string $field): string {
        $field = trim($field);

        /** Obtiene las credenciales de la base de datos desde las variables de entorno. */
        $credentials = $this->get_credentials();

        /** @var string $drive Motor de base de datos en uso. */
        $drive = $credentials->get_drive();

        return in_array($drive, self::DRIVERS)
            ? "`{$field}`"
            : "\"{$field}\"";
    }


    /**
     * Es exactamente lo mismo que `$this->from(string $tabla)`, pero un poco más
     * semántico.
     *
     * @param string $table
     * @return self
     */
    public function to(string $table): self {
        $this->from($table);
        return $this;
    }

    /**
     * Opciones de la consulta
     *
     * @return void
     */
    private function set_options(): void {
        $options = [];

        if (!($this->empty($this->where))) {
            array_push($options, " " . $this->where);
        }

        if (!is_null($this->group_by)) {
            array_push($options, "" . $this->group_by);
        }

        if (!($this->empty($this->order_by))) {
            array_push($options, $this->order_by);
        }

        /** @var string $limit */
        $limit = $this->get_limit();

        if ($limit) {
            array_push($options, $limit);
        }

        $this->options = join("", $options);
    }

    /**
     * Devuelve el límite compatible con el motor de base de datos que se esté utilizando
     *
     * @return string|null
     */
    private function get_limit(): ?string {
        $limit = $this->limit;

        $is_limit = $limit > 0 || empty(trim($limit));

        if (!$is_limit) {
            return null;
        }

        $parts = explode(",", $limit);

        if (count($parts) < 2) {
            $this->set_params("limit", (int) $limit);
            return "LIMIT :limit";
        }

        foreach ($parts as &$part) {
            if (!is_string($part)) {
                continue;
            }

            $part = trim($part);
        }

        /** @var string|int $offset */
        $offset = $parts[0];

        /** @var string|int $rows */
        $rows = $parts[1];

        $this->set_params("offset", (int) $offset)
            ->set_params("rows", (int) $rows);

        /** Credenciales del sistema */
        $credentials = $this->get_credentials();

        /** @var string $drive */
        $drive = $credentials->get_drive();

        /** @var bool $is_mysql */
        $is_mysql = in_array($drive, self::DRIVERS);

        if ($is_mysql) {
            return " LIMIT :offset, :rows";
        }

        return " LIMIT :rows OFFSET :offset";
    }

    /**
     * Condicional de la base de datos
     *
     * @param string $field
     * @param string $operator
     * @param ?string $value
     * @param string $localOperator
     * @return self
     */
    public function where(string $field, string $operator, ?string $value = NULL, string $logical = self::AND): self {
        $logical = $this->get_logical_operator($logical);

        $this->set_conditions($this->conditions, $field, $operator, $value, $logical);

        $this->where = "WHERE " . implode(" ", $this->conditions);
        return $this;
    }

    /**
     * Agrega una condición a la cláusula HAVING de la consulta SQL.
     * 
     * Este método permite agregar condiciones de filtro en las consultas SQL que se aplican después de un GROUP BY. 
     * Se utiliza para agregar restricciones a los resultados de una consulta agrupada (por ejemplo, resultados de agregaciones).
     * Este método asume que la consulta SQL ya ha realizado una operación de agrupación con GROUP BY, o que se realizará en otro punto de la consulta.
     *
     * @param string $field El nombre del campo sobre el cual se va a aplicar la condición.
     * @param string $operator El operador de comparación que se usará para la condición (por ejemplo, '=', '>', '<', etc.).
     * @param ?string $value El valor con el cual se va a comparar el campo. Puede ser nulo si no se especifica un valor, lo que puede ser útil para comparaciones como "IS NULL".
     * @param string $logical El operador lógico que se usará entre condiciones (por defecto es "AND"). 
     *                         Se puede usar "OR" para establecer una condición alternativa.
     * 
     * @return self Retorna la instancia actual del objeto, permitiendo encadenar llamadas al método.
     */

    public function having(string $field, string $operator, ?string $value = NULL, string $logical = self::AND): self {
        $logical = $this->get_logical_operator($logical);

        $this->set_conditions($this->conditions, $field, $operator, $value, $logical);

        $this->where = "HAVING " . implode(" ", $this->conditions);
        return $this;
    }


    /**
     * Permite crear una sentencia SQL personalizada para
     * posteriormente ser ejecutada.
     *
     * @param string $query Consulta personalizada SQL
     * @return self
     */
    public function query(string $query): self {
        $this->clean();
        $this->customized = true;
        $this->custom = true;
        $this->query = trim($query);

        return $this;
    }

    /**
     * Devuelve el último registro de la base de datos en función
     * de la columna seleccionada.
     *
     * @param string $column
     * @param boolean $test
     * @return string | array
     */
    public function last(string $column, bool $test = false): string | array {
        $this->queryLast = "LAST";
        $this->column = trim($column);

        $query = $this->get_query();
        $query = trim($query);

        if ($test) {
            return $query;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data !== FALSE
            ? $data
            : [];
    }

    /**
     * Selecciona el máximo valor de un campo seleccionado.
     *
     * @param string $column
     * @param boolean $test
     * @return string | array
     */
    public function max(string $column, bool $test = false): string | array {
        return $this->min_max($column, 'max', $test);
    }

    /**
     * Encuentra el valor numérmico más pequeño de una columna
     * previamente seleccionada.
     *
     * @param string $column
     * @param boolean $test
     * @return string|array
     */
    public function min(string $column, bool $test = false): string | array {
        return $this->min_max($column, 'min', $test);
    }

    /**
     * Encuentra el valor numérico mínimo o máximo en 
     * función de la opción elegida en `$mode`.
     *
     * @param string $column Columna
     * @param string $mode Se indica si se desea obtener el mínimo o máximo valor de una columna.
     * @param boolean $test Indicar si se obtiene un string para una prueba automátizada.
     * @return string|array
     */
    private function min_max(string $column, string $mode = 'min', bool $test = false): string | array {
        if ($this->empty($this->table)) {
            throw new Error("Debe seleccionar una tabla primero\n<br>");
        }

        $mode = strtoupper($mode);
        $query = "SELECT {$mode}({$column}) AS {$column} FROM {$this->table}";

        if ($test) {
            return $query;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($data)
            ? $data
            : [];
    }

    /**
     * Contabiliza la cantidad de registros almacenados en una tabla.
     *
     * @param string $column
     * @param boolean $test
     * @return string | array
     */
    public function count(string $column = "*", bool $test = false): string | array {
        $this->set_options();

        /**
         * Indica una condicional
         * 
         * @var string $where
         */
        $where = trim($this->where);

        $column = trim($column);

        /** @var string $column_name */
        $column_name = $column !== "*" ? $column : 'count';

        if ($this->empty($this->table)) {
            throw new Error("Debe seleccionar una tabla primero\n<br>");
        }

        $this->table = trim($this->table);
        $query = "SELECT COUNT({$column}) AS {$column_name} FROM {$this->table}";

        if (!empty($where)) {
            $query = "SELECT COUNT({$column}) AS {$column_name} FROM {$this->table} {$where}";
        }

        if ($test) {
            return trim($query);
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($this->param);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($data)
            ? $data
            : [];
    }

    /**
     * Ordena de forma descendiente o ascendiente los registros
     * de una tabla en función de una columna seleccionada
     *
     * @param string $columns Columnas
     * @return self
     */
    public function order_by(string ...$columns): self {
        $columns = join(", ", $columns);
        $this->order_by = " ORDER BY {$columns}";
        return $this;
    }

    /**
     * Se indica que se desea obtener el orden en forma ascendente
     * en función de la columna seleccionada.
     *
     * @return self
     */
    public function asc(): self {
        $this->order_by .= " ASC";
        return $this;
    }

    /**
     * Se indica que se desea obtener registros en forma descendente.
     *
     * @return self
     */
    public function desc(): self {
        $this->order_by .= " DESC";
        return $this;
    }

    /**
     * Devuelve una única instancia del objeto DLDatabase
     *
     * @return self
     */
    public static function get_instance(string $timezone = '+00:00'): self {
        if (!self::$instance) {
            self::$instance = new static($timezone);
        }

        self::$instance->clean();

        return self::$instance;
    }
}
