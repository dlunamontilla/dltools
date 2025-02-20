<?php

namespace DLTools\Config;

use DLRoute\Requests\DLOutput;
use DLRoute\Server\DLServer;
use Error;
use Exception;
use PDO;
use PDOException;

/**
 * Permitirá capturar todas las variables de entorno.
 * 
 * @package DLTools
 * @version 2.0.0
 * @author David E Luna <davidlunamontilla@gmail.com>
 * @copyright (c) 2022 - David E Luna M
 * @license MIT
 */
trait DLConfig {

    use DLEnvironment;

    /**
     * Establece y obtiene una conexión con el motor de base de datos.
     * @param string $timezone Zona horaria seleccionada
     * @return PDO
     */
    public function get_pdo(string $timezone = '+00:00'): PDO {

        /**
         * Credenciales críticas de conexión al servidor de base de datos.
         * 
         * @var Credentials
         */
        $credentials = $this->get_credentials();

        /**
         * Usuario de la base de datos.
         * 
         * @var string
         */
        $username = $credentials->get_username();

        /**
         * Contraseña de la base de datos.
         * 
         * @var string
         */
        $password = $credentials->get_password();

        $drive = strtolower(trim(
            $credentials->get_drive()
        ));

        /**
         * DSN de conexión
         * 
         * @var string
         */
        $dsn = $this->get_dsn($drive);

        /** @var array $options */
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $pdo = new PDO($dsn, $username, $password, $options);
        $this->set_timezone($drive, $pdo, $timezone);

        return $pdo;
    }

    /**
     * Establece la zona horaria en la sesión de la base de datos según el motor utilizado.
     *
     * Este método configura la zona horaria en la conexión activa de PDO,
     * permitiendo que las consultas y operaciones con fechas se ajusten automáticamente
     * a la zona horaria especificada.
     *
     * @param string $drive    El nombre del motor de base de datos (pgsql, mysql, mariadb).
     * @param PDO    $pdo      La instancia de la conexión PDO.
     * @param string $timezone La zona horaria en formato SQL, por defecto '+00:00'.
     * 
     * @return void
     */
    private function set_timezone(string $drive, PDO &$pdo, string $timezone = '+00:00'): void {
        switch ($drive) {
            case 'pgsql':
                $pdo->exec("SET TIME ZONE '$timezone'");
                break;
            case 'mysql':
            case 'mariadb':
                $pdo->exec("SET time_zone = '$timezone'");
                break;
        }
    }


    /**
     * Devuelve errores personalizados
     *
     * @param array|object $data Contenido de error
     * @param bool $mail Opcional. Indica si es un error de envío de correo electrónico o no.
     * @return void
     */
    protected function exception(PDOException|Exception|Error $error, bool $mail = false): void {
        header('Content-Type: application/json; charset=utf8', true, 500);

        /**
         * Credenciales
         * 
         * @var Credentials
         */
        $credentials = $this->get_credentials();

        /**
         * Indica si es modo producción o no.
         * 
         * @var boolean
         */
        $is_producton = $credentials->is_production();

        $message = $mail
            ? "Error en el envío del correo electrónico"
            : "Error en la base de datos";

        /**
         * Detalles de error
         * 
         * @var array
         */
        $error = [
            "status" => false,
            "error" => $message,
            "details" => $error
        ];

        if ($is_producton) {
            echo "Error 500";
            Logs::save('database.json', $error);
            exit;
        }

        echo DLOutput::get_json($error, true);
        exit;
    }

    /**
     * Obtiene el Data Source Name (DSN) basado en el tipo de base de datos (driver).
     * 
     * Genera un DSN que se utiliza para establecer una conexión con
     * la base de datos, basándose en las credenciales proporcionadas y el tipo de
     * base de datos especificado en el parámetro `$drive`.
     * 
     * El DSN incluye detalles como el nombre de la base de datos, el host, el puerto,
     * el conjunto de caracteres y la intercalación. Si el tipo de base de datos no
     * coincide con los casos especificados, se utilizará el DSN para MySQL por defecto.
     * 
     * @param string $drive El tipo de base de datos (`mysql`, `mariadb`, `pgsql`, `sqlite`).
     * 
     * @return string El DSN correspondiente al tipo de base de datos.
     * 
     * @throws InvalidArgumentException Si el tipo de base de datos no es reconocido.
     */
    private function get_dsn(string $drive = 'mysql'): string {

        /**
         * Obtiene las credenciales necesarias para la conexión con la base de datos.
         * 
         * Las credenciales incluyen detalles como el nombre de la base de datos, el host,
         * el puerto, el conjunto de caracteres y la intercalación.
         * 
         * @var Credentials $credentials
         */
        $credentials = $this->get_credentials();

        /** @var string $database El nombre de la base de datos. */
        $database = $credentials->get_database();

        /** @var string $host La dirección del servidor de base de datos. */
        $host = $credentials->get_host();

        /** @var int $port El puerto de conexión al servidor de base de datos. */
        $port = $credentials->get_port();

        /** @var string $charset El conjunto de caracteres utilizado en la base de datos. */
        $charset = $credentials->get_charset();

        /** @var string $collation La intercalación utilizada en la base de datos. */
        $collation = $credentials->get_collation();

        $database = trim($database);
        $database = trim($database, "\/\\");
        $database = preg_replace("/[\/\\\]+/", DIRECTORY_SEPARATOR, $database);

        /** @var string $mysql El DSN para MySQL/MariaDB. */
        $mysql = "mysql:dbname={$database};host={$host};port={$port};charset={$charset};collation={$collation}";

        /** @var string $root */
        $root = DLServer::get_document_root();

        /** @var string $sqlite_database */
        $sqlite_database = "{$root}" . DIRECTORY_SEPARATOR . $database;

        /** @var string $dsn El DSN que se genera según el tipo de base de datos. */
        $dsn = match ($drive) {
            "mysql", "mariadb" => $mysql,
            "pgsql" => "pgsql:dbname={$database};host={$host};port={$port}",
            "sqlite" => "sqlite:{$sqlite_database}",
            default => $mysql
        };

        return $dsn;
    }
}
