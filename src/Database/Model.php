<?php

namespace DLTools\Database;

use DLRoute\Requests\DLRequest;
use DLTools\Config\Credentials;
use DLTools\Config\DLConfig;
use DLTools\Config\DLValues;
use DLTools\Config\Environment;
use Error;

/**
 * Procesa las consultas de las tablas que se encuentran asociadas
 * a este modelo.
 * 
 * @package DLTools\Database
 * 
 * @version v0.1.63
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
abstract class Model {

    use DLValues;
    use DLConfig;

    /**
     * Operador AND para una consulta SQL
     * 
     * @var string
     */
    public const AND = 'AND';

    /**
     * @var string Operador OR
     */
    public const OR = 'OR';

    /**
     * Nombre de tabla definida por el programador
     *
     * @var string|null
     */
    protected static ?string $table = null;

    /**
     * Tabla predeterminada
     *
     * @var string|null
     */
    protected static ?string $table_default = null;

    /**
     * Campos de una tabla de la base de datos.
     *
     * @var array
     */
    private array $fields = [];

    /**
     * Base de datos.
     * 
     * @var DLDatabase $db
     */
    protected static ?DLDatabase $db = null;

    /**
     * Indica si se debe ordenar por una o varias columnas
     *
     * @var array|null
     */
    protected static ?array $order_by = null;

    /**
     * Indica si se ordenade forma descendente o ascendente
     *
     * @var string|null
     */
    protected static ?string $order = "desc";

    public function __construct() {
        self::init();
    }

    /**
     * Limpia el nombre de la tabla. Se debe utilizar por cada consulta completada
     *
     * @return void
     */
    protected static function clear_table(): void {
        static::$table_default = null;
    }

    /**
     * Permite establecer propiedad y valor a cualquier clase heredada.
     * 
     * @param string $field Campo
     * @param mixed $value Valor del campo.
     * @return void 
     */
    public function __set(string $field, mixed $value): void {
        $field = trim($field);

        if (is_string($value)) {
            $value = trim($value);
        }

        $this->fields[$field] = $value;
    }

    /**
     * Devuelve el valor de la propiedad.
     *
     * @param string $field
     * @return mixed
     */
    public function __get(string $field): mixed {
        /**
         * Valor de la propiedad
         * 
         * @var mixed
         */
        $value = null;

        if (!array_key_exists($field, $this->fields)) {
            return $value;
        }

        $value = $this->fields[$field];

        return $value;
    }

    /**
     * Establece el nombre de la tabla a partir de una clase utilizada como modelo
     *
     * @param string $classname Nombre de la clase
     * @return void
     */
    private static function set_table_name(string $classname): void {
        /**
         * Parte del nombre de clase.
         * 
         * @var string[]
         */
        $parts = explode("\\", $classname);

        /**
         * Nombre de clase sin nombre de espacios.
         * 
         * @var string
         */
        $class = end($parts);

        /**
         * Nombre de la tabla en el caso de que no se tome por el nombre de la clase
         * 
         * @var string|null $table_name
         */
        $table_name = null;

        if (!is_string($class)) {
            return;
        }

        /**
         * Indica si hizo match la búsqueda de nombres que empiecen por
         * mayúsculas.
         * 
         * @var boolean
         */
        $found = preg_match_all('/[A-Z][a-z]+/', $class, $matches);

        if (!$found) {
            $table_name = strtolower($classname);
        }

        /**
         * Prefijo establecido en la variable de entorno.
         * 
         * @var string
         */
        $prefix = self::get_prefix();

        /**
         * Tabla de la base de datos.
         * 
         * @var string
         */
        $table = implode("_", $matches[0]);
        $table = strtolower($table);
        $table = trim($table);

        $table = "{$prefix}{$table}";

        if (!is_null($table_name)) {
            $table = $table_name;
        }

        static::$table_default = $table;
    }

    /**
     * Devuelve el prefijo establecido en la variable de entorno.
     *
     * @return string
     */
    private static function get_prefix(): string {
        /**
         * Variables de entorno
         * 
         * @var Environment $environment
         */
        $environment = Environment::get_instance();

        /**
         * Devuelve las credenciales a partir de las variables de entorno.
         * 
         * @var Credentials $credentials
         */
        $credentials = $environment->get_credentials();

        /**
         * Prefijo que se usará en las tablas.
         * 
         * @var string
         */
        $prefix = $credentials->get_prefix();

        return trim($prefix);
    }

    /**
     * Devuelve los registros de una consulta.
     *
     * @param string $fields Opcional. Columnas de la tabla seleccionada.
     * @return array
     */
    public static function get(string ...$fields): array {
        static::init();

        /**
         * Datos de la consulta.
         * 
         * @var array
         */
        $data = [];


        if (!is_null(static::$order_by) && count(static::$order_by) > 0) {
            if (static::$order !== "desc" && static::$order !== "asc") {
                throw new Error("Solo se permiten `desc` o `asc`");
            }

            $data = static::$db->from(static::$table_default)
                ->select(...$fields)
                ->order_by(...static::$order_by)
                ->{static::$order}()
                ->limit(100)
                ->get();
        }

        if (count($data) < 1) {
            $data = static::$db->from(static::$table_default)->select(...$fields)->limit(100)->get();
        }

        static::clear_table();
        return $data;
    }

    /**
     * Inserta registro en la base de datos.
     * 
     * Si va a agregar un registro, debe hacerlo así:
     * 
     * ```
     * <?php
     * ...
     * 
     * Tabla::insert([
     *  "column" => "Contenido de la columna"
     * ]);
     * ```
     * 
     * Puede agregar múltiples registros agregando un array de array asociativos
     *
     * @param array $fields Seleccione los campos de tu tabla
     * @return boolean
     */
    public static function insert(array $fields): bool {
        static::init();

        /**
         * Indicador de inserción de datos.
         * 
         * @var boolean
         */
        $it_was_inserted = static::$db->from(static::$table_default)->insert($fields);

        static::clear_table();
        return $it_was_inserted;
    }

    /**
     * Alias del método estático `insert`.
     *
     * @param array $fields Campos de la tabla.
     * @return boolean
     */
    public static function create(array $fields): bool {
        return static::insert($fields);
    }

    /**
     * Establece una condición `WHERE` para las consultas de actualización y/o eliminación de registros.
     *
     * Este método permite construir una cláusula `WHERE` con un operador de comparación personalizado, y opcionalmente
     * puede especificar un operador lógico para combinar múltiples condiciones.
     *
     * Ejemplo de uso:
     * ```php
     * Model::where('id', '=', '10');
     * // Genera: WHERE id = :id, donde `:id` contiene 10
     * 
     * // También se puede utilizar de esta forma:
     * Model::where('id', '10'); // Genera: WHERE id = :id
     *
     * Model::where('status', 'active', null, 'OR');
     * // Genera: WHERE status = :status, donde :status contiene 'active'
     *
     * Model::where('price', '>', '100', 'AND');
     * // Genera: WHERE price > :price, donde `price` contiene `'100'`
     * ```
     *
     * @param string $field El campo de la tabla sobre el cual se aplica la condición.
     * @param string $operator El operador de comparación (por ejemplo, '=', '>', '<'). 
     *                         Si se pasa un solo argumento junto con `$field`, se toma como valor y el operador será '='.
     * @param string|null $value (Opcional) El valor a comparar. Si no se proporciona, `$operator` se considera el valor.
     * @param string $logical_operator El operador lógico para combinar múltiples condiciones (`AND` o `OR`).
     *                                  Por defecto es `AND`.
     * @return DLDatabase La instancia configurada de la base de datos con la condición aplicada.
     */
    public static function where(string $field, string $operator, ?string $value = null, string $logical_operator = self::AND): DLDatabase {
        static::init();

        /**
         * Asegura que el operador lógico esté en mayúsculas para mantener consistencai.
         * 
         * @var string $logical_operator
         */
        $logical_operator = strtoupper($logical_operator);

        /** @var DLDatabase $db */
        $db = static::$db->from(static::$table_default)->where($field, $operator, $value, $logical_operator);

        static::clear_table();
        return $db;
    }

    /**
     * Establece una condición `HAVING` para las consultas con agrupación en la base de datos.
     *
     * Este método permite construir una cláusula `HAVING` con un operador de comparación personalizado, y opcionalmente
     * puede especificar un operador lógico para combinar múltiples condiciones. La cláusula `HAVING` se utiliza para
     * filtrar los resultados después de una operación de agrupación (GROUP BY).
     *
     * Ejemplo de uso:
     * ```php
     * Model::having('id', '=', '10');
     * // Genera: HAVING id = :id, donde `:id` contiene 10
     * 
     * // También se puede utilizar de esta forma:
     * Model::having('id', '10'); // Genera: HAVING id = :id
     *
     * Model::having('status', 'active', null, 'OR');
     * // Genera: HAVING status = :status, donde :status contiene 'active'
     *
     * Model::having('price', '>', '100', 'AND');
     * // Genera: HAVING price > :price, donde `price` contiene '100'
     * ```
     *
     * @param string $field El campo de la tabla sobre el cual se aplica la condición.
     * @param string $operator El operador de comparación (por ejemplo, '=', '>', '<'). 
     *                         Si se pasa un solo argumento junto con `$field`, se toma como valor y el operador será '='.
     * @param string|null $value (Opcional) El valor a comparar. Si no se proporciona, `$operator` se considera el valor.
     * @param string $logical_operator El operador lógico para combinar múltiples condiciones (`AND` o `OR`).
     *                                  Por defecto es `AND`.
     * @return DLDatabase La instancia configurada de la base de datos con la condición aplicada.
     */
    public static function having(string $field, string $operator, ?string $value = null, string $logical_operator = self::AND): DLDatabase {
        static::init();

        /**
         * Asegura que el operador lógico esté en mayúsculas para mantener consistencia.
         * 
         * @var string $logical_operator
         */
        $logical_operator = strtoupper($logical_operator);

        /** @var DLDatabase $db */
        $db = static::$db->from(static::$table_default)->having($field, $operator, $value, $logical_operator);

        static::clear_table();
        return $db;
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
     * @return DLDatabase          Retorna la instancia actual para permitir encadenamiento de métodos.
     *
     * @since 2.0.0 Se actualizó la firma del método para aceptar un array de valores en lugar de parámetros individuales
     *                y se agregó un tercer parámetro para definir el operador lógico.
     */
    public static function where_in(string $field, array $values, string $logical = self::AND): DLDatabase {
        static::init();

        /** @var DLDatabase $db */
        $db = static::$db->from(static::$table_default)->where_in($field, $values, $logical);

        static::clear_table();
        return $db;
    }


    /**
     * Selecciona los campos de la tabla
     *
     * @param string $fields Campos
     * @param string ...$other_fields
     * @return DLDatabase
     */
    public static function select(array|string $fields = "*", string ...$other_fields): DLDatabase {
        static::init();
        $db = static::$db->from(static::$table_default)->select($fields, ...$other_fields);
        static::clear_table();
        return $db;
    }

    /**
     * Devuelve el primer registro de una consulta.
     *
     * @param string ...$fields Seleccione los campos que se mostrarán
     * @return array
     */
    public static function first(string ...$fields): array {
        static::init();

        $data = static::$db->from(static::$table_default)
            ->select(...$fields)
            ->first();

        static::clear_table();
        return $data;
    }

    /**
     * Devuelve la cantidad de registros de una tabla
     *
     * @return integer
     */
    public static function count(): int {
        static::init();

        /**
         * Registros de una tabla.
         * 
         * @var array
         */
        $data = static::$db->from(static::$table_default)->count();

        static::clear_table();
        return $data['count'] ?? 0;
    }

    /**
     * Almacena los datos en una tabla.
     *
     * @return boolean
     */
    public function save(): bool {

        if (empty($this->fields)) {
            return false;
        }

        return static::insert($this->fields);
    }

    /**
     * Ordena por columnas
     *
     * @param string ...$column Columnas
     * @return DLDatabase
     */
    public static function order_by(string ...$column): DLDatabase {
        static::init();

        $db = static::$db->from(static::$table_default)->order_by(...$column);
        static::clear_table();

        return $db;
    }

    /**
     * Estable el un sistema de paginación en el modelo
     *
     * @param integer $page Número de página
     * @param integer $rows Número de registros por páginas
     * @return array
     */
    public static function paginate(int $page = 1, int $rows = 100): array {

        static::init();
        $data = static::$db->from(static::$table_default)->paginate($page, $rows);
        static::clear_table();

        return $data;
    }

    /**
     * Inicializa la 
     *
     * @return void
     */
    protected static function init(): void {
        static::set_table_name(static::$table ?? static::class);

        /**
         * Peticiones de usuario.
         * 
         * @var DLRequest
         */
        $request = DLRequest::get_instance();

        /**
         * Entradas del usuario
         * 
         * @var string|array
         */
        $values = $request->get_values();

        if (is_array($values)) {
            static::$values = $values;
        }

        static::$db = DLDatabase::get_instance();
    }

    /**
     * Permite configurar el ordenamiento por una o varias columnas específicas y un tipo de orden, siendo 'desc' (descendente) el valor predeterminado.
     * 
     * Los valores admitidos en `$type` son: `desc` y `asc`
     * 
     * - **`desc`:** Para ordenar de forma descendente.
     * - **`asc`:** Para ordenar deforma ascendente.
     *
     * @param string $field Un string que contiene una o más columnas separadas por comas para ordenar.
     * @param string $type El tipo de orden, con 'desc' (descendente) como valor predeterminado.
     * @return void
     */
    public static function set_order(string $field, string $type = "desc"): void {
        /**
         * Patrón de búsqueda de columnas
         * 
         * @var string $pattern
         */
        $pattern = "/^[a-z][a-z0-9_]+$/i";

        /**
         * Columnas seleccionadas de una tabla
         * 
         * @var array<string> $columns
         */
        $columns = explode(",", $field);

        foreach ($columns as &$column) {
            $column = trim($column);

            if (empty($column)) {
                continue;
            }

            /**
             * Indica si el nombre de columna es inválido
             * 
             * @var boolean $is_valid
             */
            $is_valid = preg_match($pattern, $column);

            if (!$is_valid) {
                throw new Error("El nombre de la columna es inválido", 103);
            }
        }

        static::$order_by = $columns;
        static::$order = $type;
    }

    /**
     * Agrupa los resultados en función de los campos seleccionadas
     *
     * @param string ...$field Campos por el que se van a agrupar
     * @return DLDatabase
     */
    public static function group_by(string ...$field): DLDatabase {
        static::init();

        /**
         * Tabla actual elegida por el modelo
         * 
         * @var string $table
         */
        $table = static::$table_default;

        /**
         * Base de datos
         * 
         * @var DLDatabase $db
         */
        $db = static::$db->from($table)->group_by(...$field);
        static::clear_table();

        return $db;
    }

    /**
     * Establece una consulta que permite devolver registro en función de un campo con valor nulo previamente seleccionado.
     *
     * @param string $field Campo o columna con valor nulo
     * @return DLDatabase
     */
    public static function field_is_null(string $field): DLDatabase {
        static::init();

        /**
         * Tabla elegida por el modelo
         * 
         * @var string $table
         */
        $table = static::$table_default;

        /**
         * Base de datos
         * 
         * @var DLDatabase $db
         */
        $db = static::$db->from($table)->field_is_null($field);

        static::clear_table();
        return $db;
    }
}
