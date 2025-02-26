<?php

namespace DLTools\Compilers;

use DLRoute\Server\DLServer;

/**
 * Parsea las plantillas ubicadas en el directorio `resources` con
 * la ayuda de la clase `DLTemplate` y crea archivos PHP listos para
 * ejecutar.
 * 
 * @package DLTools
 * 
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @license MIT
 * @version v1.0.0
 */
class DLView {
    /**
     * Instancia de la clase DLView
     *
     * @var self|null
     */
    private static ?self $instance = NULL;

    protected function __construct() {
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Encuentra las vistas y las procesa
     *
     * @param string $view
     * @return string
     */
    public static function template(string $view, array $data = []): string {
        /**
         * Directorio raíz de la aplicación.
         * 
         * @var string
         */
        $root = DLServer::get_document_root();

        /**
         * Archivo de plantillas.
         * 
         * @var string
         */
        $filename = "{$root}/resources/{$view}.template.html";

        if (!file_exists($filename)) {
            echo self::setMessage("no existe", $filename);
            http_response_code(404);
            exit(1);
        }

        /**
         * Contenido de la plantilla que será procesada.
         * 
         * @var string|false
         */
        $stringTemplate = file_get_contents($filename);

        if ($stringTemplate === FALSE) {
            $stringTemplate = "";
        }

        $stringTemplate = DLTemplate::parseDirective($stringTemplate, $data);

        /**
         * Código compilado devuelto
         * 
         * @var string
         */
        $code = DLTemplate::build($stringTemplate);
        $code = preg_replace('/\s+/', ' ', $code);
        $code = preg_replace('/(?<=\>)\s+(?=\<)/', '', $code);

        return $code;
    }

    /**
     * Carga una vista y la procesa
     *
     * @param string $view
     * @param array $data
     * @return void
     */
    public static function load(string $view, array $data = []): void {
        $root = DLServer::get_document_root();

        $view = preg_replace("/\./", DIRECTORY_SEPARATOR, $view);

        $stringTemplate = self::template($view);

        $stringTemplate = self::trim_quote($stringTemplate);

        $filename = base64_encode($view) . ".php";

        foreach ($data as $key => $variable) {
            ${$key} = $variable;
        }

        /**
         * Directorio de almacenamiento de los archivos compilados.
         * 
         * @var string $cacheDir
         */
        $cacheDir = "{$root}/cache";

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $filename = "$cacheDir/$filename";

        if (!file_exists($filename)) {
            file_put_contents($filename, $stringTemplate);
        }

        $hashFile = hash_file('sha1', $filename);
        $hashView = hash('sha1', $stringTemplate);

        if ($hashFile !== $hashView) {
            file_put_contents($filename, $stringTemplate);
        }

        include $filename;
    }

    /**
     * Devuelve un mensaje formateado.
     *
     * @param string $message
     * @param string $template
     * @return string
     */
    private static function setMessage(string $message, string $template): string {
        $styles = "style=\"font-family: 'Open Sans', sans-serif, arial; font-weight: normal; padding: 20px; width: calc(100% - 20px); border-radius: 5px; background-color: #d00000; color: white; margin: 30px auto; max-width: 1024px\"";

        $message = "<style>:root {background-color: #333333}</style><h3 {$styles}>La plantilla <strong style=\"padding: 10px\">{$template}</strong> {$message}</h3>\n\n";
        return $message;
    }

    /**
     * Elimina las comillas
     *
     * @param string $string
     * @return string
     */
    public static function trim_quote(string $string): string {
        $string = trim($string, "\"\'\`");
        return $string;
    }
}
