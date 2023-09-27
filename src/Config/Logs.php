<?php

namespace DLTools\Config;
use DLRoute\Requests\DLOutput;
use DLRoute\Routes\RouteDebugger;
use DLRoute\Server\DLServer;

/**
 * Crea los `logs` del sistema.
 * 
 * @package DLTools\Config
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
final class Logs {

    public static function save(string $filename, mixed $data): void {
        /**
         * Raíz de la aplicación
         * 
         * @var string
         */
        $root = DLServer::get_document_root();

        /**
         * Directorio de archivos logs
         * 
         * @var string
         */
        $log_dir = "{$root}/logs";

        $log_dir = RouteDebugger::remove_trailing_slash($log_dir);

        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        /**
         * Log de destino
         * 
         * @var string
         */
        $filename = "{$log_dir}/{$filename}";

        if (is_object($data) || is_array($data)) {
            $data = DLOutput::get_json($data, true);
        }

        file_put_contents($filename, $data);
    }
}