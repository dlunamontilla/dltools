<?php

namespace DLTools\Database;

use DLTools\Config\DLConfig;
use Error;
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
     * Limpia los datos almacenados en la clase DLDatabase.
     *
     * @return void
     */
    public function clean(): void {
        $this->select = "";
        $this->update = "";
        $this->delete = "";
        $this->insert = "";

        $this->fields = "*";
        $this->where = "";
        $this->query = "";
        $this->custom = false;
        $this->param = [];
        $this->options = "";
        $this->queryLast = "";
        $this->column = "";
        $this->orderDirection = "ASC";
        $this->order_by = "";
        $this->limit = -1;
        $this->customer = false;
        $this->conditions = [];
    }

    /**
     * Selecciona los campos del formulario
     *
     * @param array|string $fields Campos de una tabla SQL
     * @param string ...$otherFields Otros campos de una tabla SQL
     * @return self
     */
    public function select(array|string $fields = "*", string ...$otherFields): self {
        $this->select = "SELECT";

        $this->fields = is_array($fields)
            ? join(', ', $fields)
            : $fields;

        if (!($this->empty($otherFields))) {
            $this->fields .= ", " . join(", ", $otherFields);
        }

        return $this;
    }

    /**
     * Actualiza los registros de una tabla
     *
     * @param boolean $test
     * @return string|bool
     */
    public function update(array $fields, bool $test = false): string | bool {
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

        $newFields = [];
        $params = [];

        foreach ($fields as $field => $value) {
            $key = ":" . $field;


            if (array_key_exists($key, $this->param)) {
                array_push($newFields, "{$field} = {$key}_v");
                $this->param[$key . "_v"] = $value;

                continue;
            }

            array_push($newFields, "{$field} = {$key}");
            $this->param[$key] = $value;
        }

        $params = $this->param;
        $query = "UPDATE {$this->table} SET " . join(", ", $newFields);

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
        /**
         * Indica si el proceso se ha completado.
         * 
         * @var boolean
         */
        $completed = false;

        $this->delete = "DELETE";

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

    public function from(string $table): self {
        $this->clean();
        $this->table = trim($table);

        $empty = empty($this->select) && empty($this->update) && empty($this->delete) && empty($this->insert);

        if ($empty) {
            $this->select();
        }

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

        if (!$this->custom) {
            $this->get_query();
        }

        if ($this->custom) {
            $this->param = $param;
        }

        if ($this->empty($this->query)) {
            $this->select();
        }

        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->param);

        $this->clean();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    /**
     * Devuelve el primero registro de la tabla
     *
     * @param array $param Parámetros de la consulta.
     * @return array
     */
    public function first(array $param = []): array {
        $data = [];

        if (!$this->custom) {
            $this->get_query();
        }

        if ($this->custom) {
            $this->param = $param;
        }

        if ($this->empty($this->query)) {
            $this->select();
        }

        if ($this->empty($this->query)) {
            throw new Error("La consulta SQL no puede estar vacía");
        }

        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->param);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->clean();

        return $data !== FALSE
            ? $data
            : [];
    }

    /**
     * Devuelve una consulta SQL construída a partir del constructor
     * de consulta de DLTools.
     *
     * @return string
     */
    public function get_query(): string {
        if ($this->customer) {
            return trim($this->query);
        }

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

        if (!($this->empty($this->select))) {
            $query = $this->select . " " . $this->fields . " FROM " . $this->table . $this->options;
        }

        if (!($this->empty($this->update))) {
            echo PHP_EOL . $this->update;
            $query = $this->update . " {$this->table}";
        }

        if (!($this->empty($this->delete))) {
            $query = $this->delete . " FROM " . $this->table . $this->options;
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
     * Inserta registros a una tabla SQL
     *
     * @param array $fields Campos del formulario
     * @param bool $test Se define si se ejecutará en modo de prueba o en modo real
     * @return string | bool
     */
    public function insert(array $fields, bool $test = false): string | bool {
        $table = $this->table;
        $this->insert = "INSERT";

        if ($this->empty($table)) {
            throw new Error("Seleccione una tabla");
        }

        $keys = [];
        $new_keys = [];
        $values = [];

        foreach ($fields as $key => $value) {

            if (is_string($key)) {
                array_push($keys, "`$key`");
                array_push($new_keys, ":$key");
                $values[":" . $key] = $value;
            }

            if (is_array($value) && is_numeric($key)) {
                $register = [];

                foreach ($value as $newKey => $newValue) {
                    if ($key === 0) {
                        array_push($keys, "`$newKey`");
                        array_push($new_keys, ":$newKey");
                    }

                    $register[":" . $newKey] = $newValue;
                }

                array_push($values, $register);
            }
        }

        $this->fields = join(", ", $keys);
        $this->values = $values;

        $this->new_keys = join(", ", $new_keys);
        $query = "{$this->insert} INTO `{$this->table}` ({$this->fields}) VALUES ({$this->new_keys})";

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

        if ($this->limit > 0) {
            array_push($options, " LIMIT {$this->limit}");
        }

        $this->options = join("", $options);
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

        /** @var bool $is_empty */
        $is_empty = empty(trim($this->where));

        $this->where = "WHERE " . implode(" ", $this->conditions);

        return $this;
    }

    /**
     * Construye la consulta condicional `where` estableciendo la condición.
     *
     * @param array $conditions Condicionales almacenadas.
     * @param string $field Campo o columna de la tabla de referencia para ser consultada.
     * @param string $operator Operador de comparación.
     * @param string|null $value Valor de la consulta.
     * @param string $logical Operador lógico.
     * @return void
     */
    private function set_conditions(array &$conditions, string $field, string $operator, ?string $value = NULL, string $logical = 'AND') {
        $operator = strtoupper(trim($operator));

        /** @var string | null $key */
        $key = $this->get_param_key($field, $operator, $value);

        /** @var string $condition */
        $condition = "";

        if (is_null($value) && !is_null($key)) {
            $operator = "=";
        }

        $condition = $key
            ? "{$field} {$operator} {$key}"
            : "{$field} {$operator}";

        if (count($conditions) > 0) {
            $condition = "{$logical} {$condition}";
        }

        $conditions[] = $condition;
    }

    /**
     * Devuelve el operador lógico
     *
     * @param string $logical Operador lógico a ser procesado
     * @return string
     */
    private function get_logical_operator(string $logical): string {
        $logical = strtoupper(trim($logical));

        /** @var string[] $allowed */
        $allowed = ["AND", "OR"];

        return in_array($logical, $allowed) ? $logical : 'AND';
    }

    /**
     * Establece y devuelve el parámetro con el valor asociado a una clave de tipo :key.
     *
     * @param string $field Campo o columna de la tabla.
     * @param string $operator Operator o valor según sea el caso.
     * @param string|null $value Valor de la consulta.
     * @return string|null
     */
    private function get_param_key(string $field, string $operator, ?string $value): ?string {

        if ($this->is_null_operator($operator)) {
            return null;
        }

        static $counters = [];

        if (!isset($counters[$field])) {
            $counters[$field] = 0;
        }

        ++$counters[$field];

        /** @var string $key */
        $key = ":{$field}" . $counters[$field];

        $this->set_param_key($key, $operator, $value);
        return $key;
    }


    /**
     * Establece la clave del parámetro de la consulta parametrizada.
     *
     * @param string $key Clave del parámetro
     * @param string $operator Operador
     * @param string|null $value Valor que se utilizar para ser consultado
     * @return void
     */
    private function set_param_key(string $key, string $operator, ?string $value): void {
        $this->param[$key] = !is_null($value)
            ? trim($value)
            : trim($operator);
    }

    /**
     * Devuelve el operador normalizado.
     *
     * @param string $operator Operador a ser procesado.
     * @return string Operador en su forma estándar.
     */
    private function get_operator(string $operator): string {
        $operator = strtoupper(trim($operator));

        return match ($operator) {
            "IS NULL", "IS NOT NULL" => $operator,
            default => $operator
        };
    }

    /**
     * Valida si se trata de un operador de nulidad
     *
     * @param string $operator Operador a verificar
     * @return boolean
     */
    private function is_null_operator(string $operator): bool {
        /** @var string[] $null_operators */
        $null_operators = ["IS NULL", "IS NOT NULL"];

        return in_array($operator, $null_operators, true);
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
        $this->customer = true;

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
        /**
         * Indica una condicional
         * 
         * @var string $where
         */
        $where = trim($this->where);

        $column = trim($column);
        $columnName = $column !== "*" ? $column : 'count';

        if ($this->empty($this->table)) {
            throw new Error("Debe seleccionar una tabla primero\n<br>");
        }

        $this->table = trim($this->table);
        $query = "SELECT COUNT({$column}) AS {$columnName} FROM {$this->table}";

        if (!empty($where)) {
            $query = "SELECT COUNT({$column}) AS {$columnName} FROM {$this->table} {$where}";
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
    public static function get_instance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
