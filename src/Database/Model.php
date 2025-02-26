<?php

namespace DLTools\Database;

use DLRoute\Requests\DLRequest;
use DLTools\Config\Credentials;
use DLTools\Config\DLConfig;
use DLTools\Config\DLValues;
use DLTools\Config\Environment;
use DLTools\Core\Data\DTO\ValueRange;
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
     * Nombre de la tabla definida por el programador.
     *
     * Esta variable permite definir manualmente la consulta SQL que se usará como origen de datos. 
     * Se utiliza en casos donde no se quiere hacer referencia directamente a una tabla, sino a 
     * una subconsulta o una vista personalizada.
     *
     * Ejemplo de uso:
     * ```php
     * protected static ?string $table = "SELECT * FROM dl_employee WHERE status = 1";
     * ```
     *
     * @var string|null
     */
    protected static ?string $table = null;

    /**
     * Nombre de la tabla predeterminada del modelo.
     *
     * Se asigna automáticamente basándose en el nombre de la clase del modelo, a menos que el 
     * programador lo sobrescriba manualmente. Se usa en métodos que requieren una referencia 
     * directa a la tabla real en la base de datos.
     *
     * Ejemplo de uso:
     * ```php
     * protected static ?string $table_default = "dl_employee";
     * ```
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

    /**
     * Propiedad estática para la zona horaria por defecto.
     *
     * Se utiliza para almacenar la zona horaria global del sistema.
     * El formato debe ser una cadena válida de desplazamiento UTC (por ejemplo, '+00:00', '-05:00').
     *
     * @var string $timezone
     */
    protected static string $timezone = '+00:00';


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
     * Ejecuta una consulta y devuelve los registros obtenidos.
     *
     * Este método inicializa la conexión a la base de datos, ejecuta la consulta sobre 
     * la tabla predeterminada y retorna los registros obtenidos. Una vez ejecutada la consulta, 
     * se limpia la referencia a la tabla para evitar reutilizaciones accidentales en futuras consultas.
     *
     * @param array $params Opcional. Array asociativo de parámetros para la consulta parametrizada.
     *                      Las claves representan los nombres de los parámetros en la consulta SQL
     *                      y los valores corresponden a los datos a sustituir. 
     *                      Esto previene inyecciones SQL y mejora la seguridad.
     *
     * @return array Retorna un array con los registros obtenidos de la consulta.
     */
    public static function get(array $params = []): array {
        static::init();

        /**
         * Datos obtenidos de la consulta.
         * 
         * @var array $data Contiene los registros resultantes de la consulta.
         */
        $data = static::$db->from(static::$table_default)->get($params);

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
     * Reemplaza un registro en la base de datos.
     * 
     * Si desea agregar o actualizar un registro, debe hacerlo así:
     * 
     * ```php
     * <?php
     * ...
     * 
     * Tabla::replace([
     *      "column" => "Contenido de la columna"
     * ]);
     * ```
     * 
     * Puede agregar múltiples registros proporcionando un array de arrays asociativos.
     * 
     * @param array $fields Conjunto de campos y valores a insertar o actualizar.
     * @param bool $test Indica si la operación debe ejecutarse en modo de prueba.
     * @return bool `true` si la operación se realizó con éxito, `false` en caso contrario.
     */
    public static function replace(array $fields, bool $test = false): bool {
        static::init();

        /**
         * Indicador de inserción o actualización de datos.
         * 
         * @var bool
         */
        $it_was_inserted = static::$db->from(static::$table_default)->replace($fields, $test);

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
     * Asigna un valor a un parámetro de la consulta parametrizada de forma estática.
     *
     * Este método inicializa la instancia de DLDatabase si aún no ha sido configurada, asigna el valor del parámetro
     * utilizando el método interno `set_params` y luego limpia la configuración de la tabla mediante `clear_table()`.
     * De esta manera, se garantiza que la consulta se construya correctamente y se permita el encadenamiento de métodos.
     *
     * Ejemplo de uso:
     * ```php
     * DLDatabase::set_params('id', '10');
     * // Esto asigna el valor '10' al parámetro :id en la consulta parametrizada.
     * ```
     *
     * @param string $key El nombre del parámetro sin el prefijo ':'.
     * @param string $value El valor que se asignará al parámetro.
     * @return DLDatabase Retorna la instancia configurada de DLDatabase para permitir el encadenamiento de métodos.
     */
    public static function set_params(string $key, string $value): DLDatabase {
        static::init();

        /** @var DLDatabase $db */
        $db = static::$db->from(static::$table_default)->set_params($key, $value);

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
     * Selecciona los campos de la tabla y devuelve una instancia de DLDatabase para continuar la construcción de la consulta.
     *
     * Este método inicializa la conexión a la base de datos y selecciona los campos especificados de la tabla predeterminada.
     * Permite encadenar otros métodos para construir consultas más complejas.
     * Al finalizar, se limpia la referencia a la tabla para evitar conflictos en futuras consultas.
     *
     * @param array|string $fields Nombre del campo o lista de campos a seleccionar. 
     *                             Si es una cadena, representa un único campo o "*" para seleccionar todos los campos.
     *                             Si es un array, debe contener los nombres de los campos a seleccionar.
     * @param string ...$other_fields Campos adicionales a seleccionar. Se pueden pasar como argumentos separados.
     *
     * @return DLDatabase Retorna una instancia de DLDatabase para permitir el encadenamiento de métodos.
     */
    public static function select(array|string $fields = "*", string ...$other_fields): DLDatabase {
        static::init();
        $db = static::$db->from(static::$table_default)->select($fields, ...$other_fields);
        static::clear_table();
        return $db;
    }


    /**
     * Obtiene el primer registro de una consulta ejecutada sobre la tabla predeterminada.
     *
     * Este método inicializa la conexión a la base de datos, ejecuta la consulta sobre la tabla definida en el modelo
     * y devuelve el primer resultado encontrado. Se pueden pasar parámetros opcionales para consultas parametrizadas,
     * lo que mejora la seguridad y evita inyecciones SQL. 
     * Una vez ejecutada la consulta, se limpia la referencia a la tabla para evitar reutilizaciones accidentales.
     *
     * @param array $params Opcional. Parámetros para la consulta parametrizada, permitiendo filtrar los resultados.
     *
     * @return array Retorna un array asociativo con los datos del primer registro encontrado.
     *               Si no se encuentra ningún resultado, devuelve un array vacío.
     */
    public static function first(array $params = []): array {
        static::init();

        $data = static::$db->from(static::$table_default)->first($params);

        static::clear_table();
        return $data;
    }


    /**
     * Devuelve la cantidad de registros de una tabla.
     *
     * Este método ejecuta una consulta `COUNT` sobre la tabla predeterminada, 
     * opcionalmente contando los registros de una columna específica. 
     * Si no se especifica una columna, contará todos los registros de la tabla.
     * Además, tiene un parámetro adicional `$test` que puede ser utilizado para 
     * realizar pruebas o consultas simuladas, sin afectar la base de datos.
     * Luego, limpia la referencia a la tabla para evitar reutilizaciones accidentales.
     *
     * @param string $column Columna sobre la cual contar los registros. Por defecto es "*" para contar todos los registros.
     * @param bool $test Si se establece como `true`, realiza una simulación de la consulta sin ejecutarla realmente.
     *
     * @return int Retorna la cantidad de registros contados. Si no se encuentra ningún registro, retorna 0.
     */
    public static function count(string $column = "*", bool $test = false): int {
        static::init();

        /**
         * Resultado de la consulta COUNT.
         * 
         * @var array $data Contiene el resultado de la consulta COUNT.
         */
        $data = static::$db->from(static::$table_default)->count($column, $test);

        static::clear_table();
        return $data['count'] ?? 0;
    }


    /**
     * Almacena los datos en una tabla.
     *
     * Este método verifica si hay campos definidos en el objeto y, si es así,
     * procede a insertar los datos en la tabla asociada utilizando la función 
     * `insert`. Si no hay campos definidos, devuelve `false` para indicar que 
     * no se pueden guardar datos.
     *
     * @return bool Retorna `true` si los datos se almacenan correctamente en la base de datos, 
     *              o `false` si no hay campos para guardar.
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
     * Establece un sistema de paginación en el modelo.
     *
     * Este método permite establecer un sistema de paginación para las consultas, 
     * especificando el número de página y la cantidad de registros por página.
     * La función también permite pasar parámetros adicionales para ajustar la consulta.
     *
     * @param int $page Número de página a obtener. El valor predeterminado es 1.
     * @param int $rows Número de registros por página. El valor predeterminado es 100.
     * @param array $param Parámetros opcionales para ajustar la consulta, como filtros o condiciones adicionales.
     *
     * @return array Retorna los resultados de la consulta con los datos paginados, 
     *              según los parámetros definidos (número de página y cantidad de registros por página).
     */
    public static function paginate(int $page = 1, int $rows = 100, array $param = []): array {
        static::init();

        $data = static::$db->from(static::$table_default)->paginate($page, $rows, $param);
        static::clear_table();

        return $data;
    }

    /**
     * Inicializa la configuración del modelo y establece los valores necesarios.
     *
     * Este método es responsable de inicializar el modelo, configurando el nombre de la tabla, 
     * obteniendo las peticiones del usuario y sus valores asociados, y configurando la instancia de la base de datos.
     * 
     * - Establece el nombre de la tabla asociada al modelo, utilizando un nombre predeterminado o el nombre de la clase.
     * - Obtiene los valores de la petición del usuario a través de `DLRequest`.
     * - Configura la instancia de la base de datos utilizando `DLDatabase`.
     *
     * @return void No retorna ningún valor. Se utiliza solo para establecer la configuración inicial.
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

        static::$db = DLDatabase::get_instance(static::$timezone);
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
     * Establece una consulta que permite devolver registros en función de un campo con valor nulo previamente seleccionado.
     *
     * Este método estático permite establecer una condición en la consulta SQL para filtrar los registros donde el valor de un campo específico sea `NULL`.
     * El campo se pasa como parámetro, y la consulta resultante incluirá la cláusula `WHERE {campo} IS NULL`.
     * 
     * Se utiliza el nombre de la tabla predeterminada del modelo y ejecuta la consulta sobre ella.
     * 
     * @param string $field El nombre del campo o columna que se evaluará para verificar si su valor es `NULL`.
     * @return DLDatabase Retorna la instancia de la clase `DLDatabase` con la consulta construida, permitiendo encadenar más métodos sobre la consulta.
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

    /**
     * Alias de `field_is_null()`, permite filtrar registros donde un campo específico sea `NULL`.
     *
     * Este método es un alias de `field_is_null()`, proporcionando una forma más corta y legible 
     * para establecer una condición en la consulta SQL que filtre los registros en los que 
     * el valor del campo especificado sea `NULL`. 
     * 
     * Internamente, delega la ejecución al método `field_is_null()`, asegurando la misma funcionalidad.
     *
     * @param string $field El nombre del campo o columna que se evaluará para verificar si su valor es `NULL`.
     * @return DLDatabase Retorna la instancia de la clase `DLDatabase` con la consulta construida, 
     *                    permitiendo encadenar más métodos sobre la consulta.
     */
    public static function is_null(string $field): DLDatabase {
        return static::field_is_null($field);
    }


    /**
     * Ejecuta una consulta SQL personalizada sobre la base de datos.
     *
     * Este método permite ejecutar cualquier consulta SQL arbitraria proporcionada como parámetro,
     * retornando el objeto de base de datos para permitir un encadenamiento de consultas.
     *
     * @param string $query Consulta SQL a ejecutar.
     *
     * @return DLDatabase Retorna el objeto de base de datos que contiene el resultado de la consulta.
     */
    public static function query(string $query): DLDatabase {
        static::init();

        /**
         * Datos obtenidos de la consulta.
         * 
         * @var array $data Contiene los registros resultantes de la consulta.
         */
        $data = [];

        // Ejecutar la consulta proporcionada
        $db = static::$db->query($query);

        static::clear_table();
        return $db;
    }

    /**
     * Aplica una condición `BETWEEN` en el campo especificado dentro del modelo.
     *
     * Este método sirve como un puente hacia `DLDatabase::between()`, permitiendo que el modelo
     * utilice la condición `BETWEEN` para filtrar registros dentro de un rango de valores.
     * 
     * ### Funcionamiento:
     * - Inicializa la conexión con la base de datos mediante `static::init()`.
     * - Llama al método `between()` de `DLDatabase`, pasando los parámetros correspondientes.
     * - Limpia la tabla actual del modelo para evitar efectos no deseados en consultas encadenadas.
     * - Retorna la instancia de `DLDatabase`, permitiendo continuar la construcción de la consulta.
     *
     * @param string $field Requerido. Nombre del campo sobre el cual se aplicará la condición.
     * @param ValueRange $range Requerido. Objeto que representa el rango de valores.
     * @param string $logical Operador lógico opcional (`AND` por defecto). Permite combinar con otras condiciones existentes.
     * 
     * @return DLDatabase Retorna la instancia de `DLDatabase`, permitiendo encadenamiento de métodos.
     */
    public static function between(string $field, ValueRange $range, string $logical = self::AND): DLDatabase {
        static::init();

        /** @var string $table */
        $table = static::$table_default;

        /** @var DLDatabase $db */
        $db = static::$db->from($table)->between(field: $field, range: $range, logical: $logical);

        static::clear_table();
        return $db;
    }
}
