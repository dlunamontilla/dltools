<?php

namespace DLTools\Config;

use DLRoute\Server\DLServer;

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
    /**
     * Ruta del directorio de trabajo
     *
     * @var string|null
     */
    private ?string $documentRoot = null;

    /**
     * Archivo que contiene las credenciales.
     * 
     * @var string $path
     */
    private string $filename;

    /**
     * Credenciales definidas en las variables de entorno
     *
     * @var array
     */
    private array $credentials = [];

    /**
     * Carga las credenciales a partir de las variables de entorno
     *
     * @return void
     */
    private function load_credentiales(): void {
        /**
         * Directorio raíz de la aplicación.
         * 
         * @var string
         */
        $root = DLServer::get_document_root();

        $this->filename = "{$root}/.env";
        
        if (!file_exists($this->filename)) {
            echo "<h2>Copie el archivo <code>.env.example</code> en <code>.env</code></h2>\n";
            exit;
        }
    
        // Se cargan las variables de entorno:
        $this->credentials = $this->env();
    }

    /**
     * Get the filename with full path.
     * 
     * @return string
     */
    public function get_path(): string {
        return trim($this->filename);
    }

    /**
     * Devuelve las credenciales del archivo .env.
     * 
     * @return array
     */
    private function env(): array {
        if (!file_exists($this->filename))
            return [];

        /**
         * @var array
         */
        $credentials = [];

        // Obtenemos las líneas del archivo:
        $lines = file($this->filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            list($name, $value) = explode("=", $line, 2);

            $name = trim($name);
            $value = trim($value);

            putenv(sprintf("%s=%s", $name, $value));

            $credentials[$name] = $value;
        }

        return $credentials;
    }

    /**
     * Devuelve las credenciales almacenadas en .env
     * @return object
     */
    public function get_credentials(): object {
        $this->load_credentiales();
        return (object) $this->credentials;
    }

    /**
     * Establece y obtiene una conexión con el motor de base de datos.
     * @return \PDO
     */
    public function get_pdo(): \PDO {
        $this->load_credentiales();
        $username = getenv("DL_DATABASE_USER");
        $password = getenv("DL_DATABASE_PASSWORD");
        $database = getenv("DL_DATABASE_NAME");
        $host = getenv("DL_DATABASE_HOST");
        $drive = getenv("DL_DATABASE_DRIVE");

        /**
         * @var string
         */
        $dsn = "$drive:dbname=$database;host=$host";
        
        try {
            $pdo = new \PDO($dsn, $username, $password);
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo "<h2>" . $e->getMessage() . "</h2>";
            exit;
        }

        return $pdo;
    }
}
