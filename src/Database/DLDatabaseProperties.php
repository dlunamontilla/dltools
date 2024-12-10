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
    protected array|string $fields = "*";

    /**
     * Nombre de la tabla con la que se va a interactuar al momento de ejecutar
     * una consulta SQL.
     *
     * @var string
     */
    protected string $table = "";


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
    protected bool $customer = false;

    protected function __construct() {
        $this->pdo = $this->get_pdo();
        $this->clean();
    }
}
