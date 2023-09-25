<?php

namespace DLTools\Database;

use DLRoute\Requests\DLRequest;
use DLTools\Config\DLConfig;

/**
 * Procesa las consultas de las tablas que se encuentran asociadas
 * a este modelo.
 * 
 * @package DLTools\Database
 * 
 * @version 1.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
abstract class Model {

    use DLConfig;

    /**
     * Nombre de la tabla de la base de datos.
     *
     * @var string|null
     */
    protected static ?string $table = null;

    /**
     * Campos de una tabla de la base de datos.
     *
     * @var array
     */
    private array $fields = [];

    /**
     * Valores de los parámetros de la petición, incluyendo, formato JSON
     *
     * @var array
     */
    private array $values = [];

    public function __construct() {
        $classname = get_class($this);
        $this->set_table_name($classname);

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
            $this->values = $values;
        }
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
    private function set_table_name(string $classname): void {
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
            return;
        }

        /**
         * Prefijo establecido en la variable de entorno.
         * 
         * @var string
         */
        $prefix = $this->get_prefix();

        /**
         * Tabla de la base de datos.
         * 
         * @var string
         */
        $table = implode("_", $matches[0]);
        $table = strtolower($table);
        $table = trim($table);

        $table = "{$prefix}{$table}";

        self::$table = $table;
    }

    /**
     * Devuelve el prefijo establecido en la variable de entorno.
     *
     * @return string
     */
    private function get_prefix(): string {
        /**
         * Prefijo que se usará en las tablas.
         * 
         * @var string
         */
        $prefix = "";

        /**
         * Devuelve las credenciales a partir de las variables de entorno.
         * 
         * @var object
         */
        $credentials = $this->get_credentials();

        /**
         * Indicador de existencia de prefijos.
         * 
         * @var boolean
         */
        $prefix_exists = isset($credentials->DL_PREFIX) && is_string($credentials->DL_PREFIX);

        if ($prefix_exists) {
            $prefix = $credentials->DL_PREFIX;
        }

        return trim($prefix);
    }

    /**
     * Devuelve los registros de una consulta.
     *
     * @param string $fields Opcional. Columnas de la tabla seleccionada.
     * @return array
     */
    public static function get(string ...$fields): array {

        /**
         * Base de datos.
         * 
         * @var DLDatabase
         */
        $db = DLDatabase::get_instance();

        /**
         * Datos de la consulta.
         * 
         * @var array
         */
        $data = $db->from(static::$table)->select(...$fields)->get();

        return $data;
    }

    /**
     * Inserta registro en la base de datos.
     *
     * @param array $fields
     * @return boolean
     */
    public static function insert(array $fields): bool {
        /**
         * Base de datos.
         * 
         * @var DLDatabase
         */
        $db = DLDatabase::get_instance();

        /**
         * Indicador de inserción de datos.
         * 
         * @var boolean
         */
        $it_was_inserted = $db->from(static::$table)->insert($fields);

        return $it_was_inserted;
    }

    /**
     * Alias del método `insert`.
     *
     * @param array $fields Campos de la tabla.
     * @return boolean
     */
    public static function create(array $fields): bool {
        return self::insert($fields);
    }

    /**
     * Establece una condicional para las consultas de actualización y/o eliminación de registros.
     *
     * @param string $field Campo de la tabla
     * @param string $operator Operador de comparación. El operador puede ser tomado como valor si se pasan dos argumentos.
     * @param string|null $value Opcional. Valor a ser evaluado
     * @return DLDatabase
     */
    public static function where(string $field, string $operator, ?string $value = null): DLDatabase {
        /**
         * Base de datos
         * 
         * @var DLDatabase
         */
        $db = DLDatabase::get_instance();

        return $db->from(static::$table)->where($field, $operator, $value);
    }

    /**
     * Selecciona los campos de la tabla
     *
     * @param string $fields Campos
     * @param string ...$other_fields
     * @return DLDatabase
     */
    public static function select(array|string $fields = "*", string ...$other_fields): DLDatabase {
        /**
         * Base de datos
         * 
         * @var DLDatabase
         */
        $db = DLDatabase::get_instance();

        return $db->from(static::$table)->select($fields, ...$other_fields);
    }

    /**
     * Devuelve el primer registro de la consulta.
     *
     * @param array $params Parámetros de la consulta
     * @return array
     */
    public static function first(array $params = []): array {
        /**
         * Base de datos.
         * 
         * @var DLDatabase
         */
        $db = DLDatabase::get_instance();

        return $db->from(static::$table)->first($params);
    }

    /**
     * Devuelve la cantidad de registros de una tabla
     *
     * @return integer
     */
    public static function count(): int {
        /**
         * Base de datos.
         * 
         * @var DLDatabase
         */
        $db = DLDatabase::get_instance();

        /**
         * Registros de una tabla.
         * 
         * @var array
         */
        $data = $db->from(static::$table)->count();

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

        return self::insert($this->fields);
    }

    /**
     * Devuelve el valor de un campo seleccionado.
     *
     * @param string $name Campo seleccionado por el usuario
     * @return string
     */
    public function get_input(string $name): mixed {

        if (!array_key_exists($name, $this->values)) {
            return null;
        }

        /**
         * Entrada del usuario.
         * 
         * @var mixed
         */
        $value = $this->values[$name] ?? null;

        return $value;
    }
}