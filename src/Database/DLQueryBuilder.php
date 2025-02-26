<?php

namespace DLTools\Database;

use DLTools\Config\DLConfig;
use DLTools\Core\Data\DTO\ValueRange;
use Exception;

/**
 * Ayuda a validar las entradas del usuario.
 * 
 * @package DLTools\Database
 * 
 * @version v0.1.63
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
trait DLQueryBuilder {

    use DLDatabaseProperties, DLConfig;

    /**
     * Establece el límite o rango de registros a devolver.
     *
     * @param integer $start Indica la cantidad de registros a mostrar si es la única con valor, de lo contrario, indica
     * desde dónde empezar.
     * @param integer|null $rows Opcional. Indica la cantidad de registros a mostrar.
     * @return static
     */
    public function limit(int $start, ?int $rows = null): static {
        $credentials = $this->get_credentials();

        /** @var string $drive */
        $drive = $credentials->get_drive();

        if ($start < 0) {
            return $this;
        }

        $this->limit = $start;

        $is_range = $start >= 0 && (!is_null($rows) && $rows >= 0);

        if ($is_range) {
            $this->limit = "{$start}, {$rows}";
        }

        return $this;
    }

    /**
     * Todavía no disponible, pero se utilizará para unir tablas en el constructor de consultas
     *
     * @param string $table
     * @return self
     */
    public function inner(string $table): self {
        return $this;
    }

    /**
     * Devuelve una lista de registros por página
     *
     * @param integer $page
     * @param integer $rows
     * @param array<string,mixed> $param Parámetros de la consulta
     * @return array
     */
    public function paginate(int $page, int $rows, array $param = []): array {

        if ($page < 1) {
            $page = 1;
        }

        if ($rows < 1) {
            $rows = 10;
        }


        $this->load_table();

        /**
         * Identifica la cantidad de registros existentes.
         * 
         * @var array|integer $quantity
         */
        $quantity = 1;
        $data_quantity = $this->count();
        $quantity = $data_quantity['count'] ?? 0;

        /**
         * Cantidad de registro de comienzo.
         * 
         * @var int
         */
        $start = $rows * ($page - 1);

        /**
         * Número de paginas calculadas en función de la cantidad registros en la tabla
         * divida por la cantidad de registros por página (`$rows`).
         * 
         * @var integer
         */
        $pages = ceil($quantity / $rows);

        /**
         * Registro de la consulta.
         * 
         * @var array
         */
        $register = [];

        /**
         * Datos vacío.
         * 
         * @var array $empty_data
         */
        $empty_data = [
            "pages" => 1,
            "page" => 1,
            "pagination" => "1 de 1",
            "rows" => 1,
            "total" => $quantity,
            "register" => []
        ];

        if ($quantity < 1) {
            return $empty_data;
        }

        if (count($register) < 1) {
            $register = $this->limit($start, $rows)->get($param);
        }

        if ($quantity <= 0) {
            $pages = 1;
            $rows = 1;
            $page = 1;
        }

        /**
         * Datos de la consulta.
         * 
         * @var array $data
         */
        $data = [
            "pages" => $pages,
            "page" => $page,
            "pagination" => "{$page} de {$pages}",
            "rows" => $rows,
            "total" => $quantity,
            "register" => $register
        ];

        return $data;
    }

    /**
     * Agrupa por
     *
     * @param string $column_name
     * @return self
     */
    public function group_by(string ...$column_name): DLDatabase {

        /**
         * Consulta SQL
         * 
         * @var string $query
         */
        $query = $this->query;

        /**
         * Cadena de columna por la cual se va a agrupar la consulta
         * 
         * @var string $columns
         */
        $columns = implode(', ', $column_name);
        $columns = trim($columns);
        $this->group_by = " GROUP BY {$columns}";

        return $this;
    }

    /**
     * Establece la consulta SQL a ejecutar
     *
     * @param string $query Consulta SQL
     * @return void
     */
    protected function set_query(string $query): void {
        $this->query = $query;
    }

    /**
     * Establece una consulta que permite devolver registros en función de un campo con valor nulo previamente seleccionado.
     *
     * Este método permite establecer una condición en la consulta SQL para filtrar los registros donde el valor de un campo específico sea `NULL`.
     * El campo se pasa como parámetro, y la consulta resultante incluirá la cláusula `WHERE {campo} IS NULL`.
     * 
     * @param string $field El nombre del campo o columna que se evaluará para verificar si su valor es `NULL`.
     * @return DLDatabase Retorna la instancia de la clase `DLDatabase` para permitir encadenar más métodos sobre la consulta.
     */
    public function field_is_null(string $field): DLDatabase {
        $field = trim($field);
        $field = trim($field, "\"\'");
        $this->where = "WHERE {$field} IS NULL";

        return $this;
    }

    /**
     * Agrega una condición "WHERE IN" a la consulta SQL.
     *
     * Este método permite especificar una condición "WHERE IN" para filtrar
     * resultados según un conjunto de valores en un campo específico de la base de datos.
     *
     * Ejemplo de uso:
     * ```
     * <?php
     * $queryBuilder->where_in('campo', ['valor1', 'valor2', 'valor3']);
     * ```
     *
     * Generará una cláusula SQL similar a:
     * ```
     * <?php
     * WHERE campo IN (':in_campo1', ':in_campo2', ':in_campo3')
     * ```
     * 
     * Donde `:in_campo1`, `:in_campo2` y `:in_campo3` es el marcador de posición de cada valor
     *
     * @param string   $field   El nombre del campo sobre el cual se aplicará la condición.
     * @param string[] $values  Lista de valores para la cláusula "IN".
     * @param string   $logical Operador lógico para combinar condiciones (por defecto, DLDatabase::AND).
     * @return DLDatabase   Devuelve una instancia DLDatabase para permitir el encadenamiento.
     *
     * @since v0.1.63 Se actualizó la firma del método para aceptar un array de valores en lugar de parámetros individuales
     *                y se agregó un tercer parámetro para definir el operador lógico.
     */
    public function where_in(string $field, array $values, string $logical = DLDatabase::AND): DLDatabase {
        $string_values = $this->get_string_values($field, ...$values);

        /** @var string $condition */
        $condition = "{$field} IN ({$string_values})";

        $logical = $this->get_logical_operator($logical);

        if (count($this->conditions) > 0) {
            $condition = "{$logical} {$condition}";
        }

        $this->conditions[] = $condition;
        $this->where = "WHERE " . implode(" ", $this->conditions);

        return $this;
    }

    /**
     * Establece una condición `BETWEEN` para un campo dentro de un rango de valores.
     * 
     * Este método agrega una cláusula `BETWEEN` a la consulta, permitiendo filtrar registros 
     * cuyos valores estén dentro del rango definido por `$range`. Los valores se asignan como 
     * parámetros nombrados para garantizar seguridad y evitar inyección SQL.
     * 
     * ### Funcionamiento:
     * - Se generan nombres únicos para los parámetros (`from_X` y `to_X`).
     * - Se establecen los valores del rango en los parámetros de la consulta.
     * - Se aplica el formato correcto al nombre del campo según el motor de base de datos.
     * - Se añade la condición a la cláusula `WHERE`, respetando la lógica establecida.
     * 
     * @param string $field Requerido. Nombre del campo sobre el cual se aplicará la condición.
     * @param ValueRange $range Requerido. Objeto que representa el rango de valores.
     * @param string $logical Operador lógico opcional (`AND` por defecto). Permite combinar con otras condiciones existentes.
     * 
     * @return DLDatabase Retorna la instancia actual para permitir encadenamiento de métodos.
     * 
     * @throws InvalidArgumentException Si el campo `$field` está vacío.
     */
    public function between(string $field, ValueRange $range, string $logical = DLDatabase::AND): DLDatabase {
        static $quantity = 0;
        ++$quantity;

        /** @var string $from_name Nombre del parámetro para el valor inicial del rango */
        $from_name = "from_{$quantity}";

        /** @var string $to_name Nombre del parámetro para el valor final del rango */
        $to_name = "to_{$quantity}";

        $this->set_params($from_name, $range->from);
        $this->set_params($to_name, $range->to);

        $field = $this->get_field($field);

        /** @var string $condition Condición BETWEEN generada */
        $condition = "{$field} BETWEEN :{$from_name} AND :{$to_name}";

        /** @var string $logical Operador lógico validado */
        $logical = $this->get_logical_operator($logical);

        if (count($this->conditions) > 0) {
            $condition = "{$logical} {$condition}";
        }

        $this->conditions[] = $condition;
        $this->where = "WHERE " . implode(" ", $this->conditions);

        return $this;
    }


    /**
     * Convierte una lista de valores en una cadena separada por comas y entrecomillada.
     *
     * Este método toma una lista de valores y los formatea como una cadena de texto
     * en la que cada valor está encerrado entre comillas simples, separados por comas.
     *
     * Ejemplo de uso:
     * ```php
     * $result = $this->get_string_values('valor1', 'valor2', 'valor3');
     * // Resultado: "'valor1', 'valor2', 'valor3'"
     * ```
     *
     * @param string ...$values Una lista de valores a formatear.
     * @return string Una cadena de texto con los valores formateados para su uso en consultas SQL.
     */
    protected function get_string_values(string $field = 'field', string ...$values): string {
        /** @var string[] $fragments */
        $fragments = [];

        /** @var int $index */
        $index = 0;

        foreach ($values as $value) {
            ++$index;

            /** @var string $key */
            $key = ":in_{$field}{$index}";
            array_push($fragments, $key);

            $this->param[$key] = $value;
        }

        return implode(", ", $fragments);
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
    protected function set_conditions(array &$conditions, string $field, string $operator, ?string $value = NULL, string $logical = 'AND') {
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
    protected function get_logical_operator(string $logical): string {
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
    protected function get_param_key(string $field, string $operator, ?string $value): ?string {

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
    protected function set_param_key(string $key, string $operator, ?string $value = null): void {
        $this->param[$key] = !is_null($value)
            ? trim($value)
            : trim($operator);
    }

    /**
     * Asigna un valor a un parámetro de la consulta parametrizada.
     *
     * Este método agrega un par clave-valor al array de parámetros utilizado en consultas parametrizadas.
     * La clave se formatea automáticamente anteponiendo dos puntos (':') para cumplir con la sintaxis SQL de parámetros.
     *
     * Ejemplo de uso:
     * ```php
     * $db->set_params('id', '10');
     * // Esto asigna el valor '10' al parámetro :id en la consulta.
     * ```
     *
     * @param string $key Nombre del parámetro sin el prefijo ':'.
     * @param string $value Valor asignado al parámetro.
     * @return DLDatabase Retorna la instancia actual de DLDatabase para permitir el encadenamiento de métodos.
     */
    public function set_params(string $key, string $value): DLDatabase {
        $this->set_param_key(":{$key}", $value);
        return $this;
    }


    /**
     * Devuelve el operador normalizado.
     *
     * @param string $operator Operador a ser procesado.
     * @return string Operador en su forma estándar.
     */
    protected function get_operator(string $operator): string {
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
    protected function is_null_operator(string $operator): bool {
        /** @var string[] $null_operators */
        $null_operators = ["IS NULL", "IS NOT NULL", "IS TRUE", "IS FALSE"];

        return in_array($operator, $null_operators, true);
    }

    /**
     * Carga el nombre de la tabla a partir de una consulta SQL.
     *
     * Este método analiza la consulta almacenada en la propiedad `$this->query` para extraer el alias
     * de la tabla. Inicialmente, intenta capturar el alias utilizando una subconsulta encerrada en 
     * paréntesis con la cláusula `as`. Si no se encuentra dicha coincidencia, se intenta extraer el 
     * nombre de la tabla utilizando la cláusula `FROM`.
     *
     * Si la propiedad `$this->table` ya contiene un valor o si la consulta está vacía, el método termina sin acción.
     *
     * @return void
     */
    protected function load_table(): void {
        $this->table = $this->extract_table($this->query);
    }

    /**
     * Obtiene el nombre de la tabla asociada a la consulta SQL o asigna un alias temporal.
     *
     * Si la propiedad `table` no es una cadena válida, se devuelve la consulta SQL proporcionada
     * envuelta en paréntesis con un alias único `current_tableN`, donde `N` es un contador incremental.
     * En caso contrario, se retorna el nombre de la tabla almacenado en la propiedad `table`.
     *
     * @param string|null $query La consulta SQL a evaluar si no hay una tabla definida.
     * @return string El nombre de la tabla o un alias temporal único si no está definido.
     */
    public function extract_table(?string $query = null): string {
        static $count = 0;

        $table = $this->table;

        if (is_string($table)) {
            $table = trim($table);
        }

        if (is_string($table) && preg_match("/^SELECT/i", $table)) {
            $table = "({$table}) AS current_table_" . ++$count;
        }

        return is_string($table) ? $table : "($query) as current_table_" . ++$count;
    }


    /**
     * Restablece el estado interno de la clase DLDatabase.
     *
     * Reinicializa todas las propiedades de la clase a sus valores por defecto,
     * asegurando que las consultas previas no afecten futuras operaciones.
     * Se utiliza después de ejecutar una consulta para evitar efectos colaterales.
     *
     * @return void
     */
    protected function clean(): void {
        // Reinicia los comandos SQL
        $this->select = "";
        $this->update = "";
        $this->delete = "";
        $this->insert = "";

        // Restablece las propiedades de la consulta
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
        $this->customized = false;
        $this->conditions = [];
        $this->operation = null;
    }

    /**
     * Carga la operación SQL actual o establece un valor por defecto.
     *
     * Si la operación SQL aún no ha sido definida, se asigna `self::SELECT` como valor predeterminado.
     * Este método no retorna ningún valor, solo asegura que la propiedad `$operation` tenga un valor válido.
     */
    protected function load_operation(): void {
        /** @var string|null $operation */
        $operation = $this->operation;

        if (is_null($operation)) {
            $this->operation = self::SELECT;
        }
    }

    /**
     * Establece la operación SQL para la consulta actual.
     *
     * Este método define la operación SQL que se ejecutará (SELECT, INSERT, UPDATE, DELETE, etc.).
     * Una vez que la operación ha sido establecida, no puede modificarse nuevamente en la misma instancia,
     * y cualquier intento de hacerlo generará una excepción.
     *
     * @param string $operation La operación SQL a establecer. Por defecto, se asigna `self::SELECT`.
     * 
     * @throws Exception Si la operación ya ha sido definida previamente.
     * 
     * @return void
     */
    protected function set_operation(string $operation = self::SELECT): void {

        if (!is_null($this->operation)) {
            throw new Exception("No se puede modificar la operación una vez establecida.", 500);
        }

        $this->operation = $operation;
    }


    /**
     * Obtiene la operación SQL actual.
     *
     * Retorna la operación SQL establecida por el usuario (SELECT, UPDATE, DELETE, etc.).
     * Si no se ha definido ninguna operación, asigna y devuelve `self::SELECT` como valor por defecto.
     *
     * @return string La operación SQL actual o `self::SELECT` si no se ha especificado ninguna.
     */
    protected function get_operation(): string {
        return $this->operation ??= self::SELECT;
    }


    /**
     * Verifica si la operación actual es una consulta SELECT.
     *
     * @return bool Retorna `true` si la operación es `SELECT`, de lo contrario, `false`.
     */
    protected function is_select(): bool {
        return $this->operation == self::SELECT;
    }

    /**
     * Verifica si la operación actual es una actualización (UPDATE).
     *
     * @return bool Retorna `true` si la operación es `UPDATE`, de lo contrario, `false`.
     */
    protected function is_update(): bool {
        return $this->operation == self::UPDATE;
    }

    /**
     * Verifica si la operación actual es una eliminación (DELETE).
     *
     * @return bool Retorna `true` si la operación es `DELETE`, de lo contrario, `false`.
     */
    protected function is_delete(): bool {
        return $this->operation == self::DELETE;
    }

    /**
     * Verifica si la operación actual es una inserción (INSERT).
     *
     * @return bool Retorna `true` si la operación es `INSERT`, de lo contrario, `false`.
     */
    protected function is_insert(): bool {
        return $this->operation == self::INSERT;
    }

    /**
     * Condiciones de la consulta
     *
     * @var array<int, string> $conditions
     */
    public array $conditions = [];

    protected function __construct(string $timezone = '+00:00') {
        $this->pdo = $this->get_pdo($timezone);
        $this->clean();
    }

    /**
     * Establece si la consulta actual debe mostrar las tablas de la base de datos.
     *
     * @param bool $show Indica si se debe activar la visualización de tablas. Por defecto, es `false`.
     * @return void
     */
    public function set_show_tables(bool $show = true): void {
        $this->show_tables = $show;
    }
}
