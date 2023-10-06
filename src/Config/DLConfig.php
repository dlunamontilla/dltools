<?php

namespace DLTools\Config;

use DLRoute\Requests\DLOutput;
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
     * @return PDO
     */
    public function get_pdo(): PDO {

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

        /**
         * Nombre de la base de datos
         * 
         * @var string
         */
        $database = $credentials->get_database();

        /**
         * Servidor de ejecución del motor de base de datos.
         * 
         * @var string
         */
        $host = $credentials->get_host();

        /**
         * Motor de base de datos seleccionada.
         * 
         * @var string
         */
        $drive = $credentials->get_drive();

        /**
         * Puerto de la base de datos.
         * 
         * @var integer
         */
        $port = $credentials->get_port();

        /**
         * Codificación de caracteres de la base de datos.
         * 
         * @var string
         */
        $charset = $credentials->get_charset();

        /**
         * Colación de la base de datos.
         * 
         * @var string
         */
        $collation = $credentials->get_collation();

        /**
         * DSN de conexión
         * 
         * @var string
         */
        $dsn = "{$drive}:dbname={$database};host={$host};port={$port};charset={$charset};collation={$collation}";

        $error_mode = PDO::ERRMODE_EXCEPTION;

        try {
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => $error_mode
            ]);

            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // return $pdo;
        } catch (PDOException | Error $error) {
            $this->exception($error);
        }

        return $pdo;
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
}
