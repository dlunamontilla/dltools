<?php

namespace DLTools\Database;

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

    use DLDatabaseProperties;

    /**
     * Establece el límite o rango de registros a devolver.
     *
     * @param integer $start Indica la cantidad de registros a mostrar si es la única con valor, de lo contrario, indica
     * desde dónde empezar.
     * @param integer|null $rows Opcional. Indica la cantidad de registros a mostrar.
     * @return static
     */
    public function limit(int $start, ?int $rows = null): static {
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
            static::error("\$page debe ser mayor que cero (0)");
        }

        if ($rows < 1) {
            static::error("\$rows debe ser mayor que cero (0)");
        }

        /**
         * Identifica la cantidad de registros existentes.
         * 
         * @var array|integer $quantity
         */
        $quantity = 0;
        $quantity = $this->count();

        $quantity = $quantity['count'] ?? 0;

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
            "rows" => 0,

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
            $rows = 0;
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
     * Establece una consulta que permite devolver registro en función de un campo con valor nulo previamente seleccionado.
     *
     * @param string $field Campo o columna con valor nulo
     * @return DLDatabase
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
        $null_operators = ["IS NULL", "IS NOT NULL"];

        return in_array($operator, $null_operators, true);
    }
}
