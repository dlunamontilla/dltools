<?php

namespace DLTools\Database;

/**
 * Continene las propiedades y en algunos casos, métodos de la base de datos.
 * 
 * @package DLTools\Database;
 * 
 * @version 1.0.0
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
trait DLDatabaseProperties {

    /**
     * Constante que representa la operación de inserción en una consulta SQL.
     * 
     * Esta constante se utiliza para indicar que la operación que se va a realizar es una inserción de datos en la base de datos.
     * Se utiliza en consultas de tipo `INSERT INTO`.
     * 
     * @var string
     */
    protected const INSERT = 'INSERT';

    /**
     * Define la acción de reemplazo en las consultas SQL.
     *
     * Esta constante se utiliza para especificar que la operación en una consulta SQL debe
     * ser un reemplazo en lugar de una inserción o actualización normal. El comando `REPLACE`
     * funciona similar a un `INSERT`, pero si un registro con una clave primaria existente
     * ya está presente, este registro será reemplazado por el nuevo, en lugar de causar un error
     * de clave duplicada.
     *
     * En este ejemplo, si los valores para la clave primaria ya existen, el registro será reemplazado
     * por los nuevos valores.
     *
     * @var string
     */
    protected const REPLACE = 'REPLACE';


    /**
     * Constante que representa la operación de selección en una consulta SQL.
     * 
     * Esta constante se utiliza para indicar que la operación que se va a realizar es una consulta de selección de datos desde la base de datos.
     * Se utiliza en consultas de tipo `SELECT`.
     * 
     * @var string
     */
    protected const SELECT = 'SELECT';

    /**
     * Constante que representa la operación de actualización en una consulta SQL.
     * 
     * Esta constante se utiliza para indicar que la operación que se va a realizar es una actualización de datos en la base de datos.
     * Se utiliza en consultas de tipo `UPDATE`.
     * 
     * @var string
     */
    protected const UPDATE = 'UPDATE';

    /**
     * Constante que representa la operación de eliminación en una consulta SQL.
     * 
     * Esta constante se utiliza para indicar que la operación que se va a realizar es una eliminación de datos en la base de datos.
     * Se utiliza en consultas de tipo `DELETE`.
     * 
     * @var string
     */
    protected const DELETE = 'DELETE';

    /**
     * Motores de base de datos compatibles con la sentencia REPLACE INTO.
     *
     * @const string[] DRIVERS Lista de motores soportados.
     */
    protected const DRIVERS = ['mysql', 'mariadb'];

    /**
     * Indica si la consulta actual es para obtener la lista de tablas de la base de datos.
     *
     * @var bool $show_tables
     */
    protected bool $show_tables = false;

    /**
     * Objeto PDO
     *
     * @var \PDO
     */
    protected \PDO $pdo;

    /**
     * Se almacena la definición de una estructura condicional en una estructura SQL.
     *
     * @var string
     */
    protected string $where = "";

    /**
     * Determina el límite de registros a devolver
     *
     * @var integer
     */
    protected int|string $limit = -1;

    /**
     * Lugar donde se define si la estructura SQL se trata de una actualización.
     *
     * @var string
     */
    protected string $update = "";

    /**
     * Lugar donde almacena la definción de una estructura SQL para la eliminación de registros.
     *
     * @var string
     */
    protected string $delete = "";

    /**
     * Lugar donde se almacena la definición de la estructura SQL para insertar nuevos registros.
     *
     * @var string
     */
    protected string $insert = "";

    /**
     * Definición de la estructura SQL donde se define la consulta de datos en una tabla.
     *
     * @var string
     */
    protected string $select = '';

    /**
     * Los campos del formulario que se van a utilizar para 
     *
     * @var array|string
     */
    public array|string $fields = "*";

    /**
     * Nombre de la tabla con la que se va a interactuar al momento de ejecutar
     * una consulta SQL.
     *
     * @var string|null
     */
    protected ?string $table = null;


    /**
     * Almacén de una estructura SQL.
     *
     * @var string
     */
    protected string $query = "";

    /**
     * Almacena información en el que deben agruparse las columnas
     *
     * @var string|null
     */
    protected ?string $group_by = null;

    protected string $new_keys = "";

    protected array $values = [];

    /**
     * Parámetros de una consulta SQL
     *
     * @var array
     */
    protected array $param = [];

    /**
     * Permite determinar si el usuario ha creado una consulta
     * personalizada.
     *
     * @var boolean
     */
    protected bool $custom = false;

    /**
     * Opciones adicionales de la consulta SQL
     *
     * @var array
     */
    protected string $options = "";

    /**
     * Permite indicar el tipo de consulta que se quiere generar.
     * En este caso, el último registro de una tabla en función de
     * su columna.
     *
     * @var string
     */
    protected string $queryLast = "";

    /**
     * Es la columna con el valor máximo seleccionado.
     */
    protected string $column = "";

    /**
     * Contiene parte de la información que ayudará a armar la sentencia SQL.
     * En este caso, el parámetro que indica al motor de base de datos que 
     * ordenen los registros de la tabla en función de la columna
     *
     * @var string
     */
    protected string $order_by = "";

    /**
     * Dirección de ordenamiendo de los registros de la tabla.
     *
     * @var string
     */
    protected string $orderDirection = "ASC";


    /**
     * Ayuda a determinar si la consulta es personalizada o no
     *
     * @var boolean
     */
    protected bool $customized = false;

    /**
     * Almacena la operación actual de la consulta (SELECT, UPDATE, DELETE, etc.).
     * 
     * Se utiliza para determinar qué tipo de operación se realizará en la consulta SQL.
     * 
     * Por defecto, la operación es una cadena vacía, pero se establece explícitamente cuando se elige una operación 
     * como `select()`, `update()`, `delete()`, entre otras.
     *
     * @var string|null $operation
     */
    protected ?string $operation = null;

    /**
     * Propiedad que define la operación por defecto que se utilizará en la consulta.
     * 
     * Si no se ha establecido una operación explícita mediante métodos como `select()`, `update()`, `delete()`, 
     * esta propiedad se utiliza para asignar el valor predeterminado, que en este caso es "SELECT".
     *
     * @var string
     */
    protected string $default_operation = "SELECT";
}
