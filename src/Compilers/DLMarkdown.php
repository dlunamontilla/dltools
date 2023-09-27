<?php

namespace DLTools\Compilers;

use DLRoute\Server\DLServer;

/**
 * Permite parsear archivos Markdown con la ayuda de una 
 * biblioteca.
 * 
 * @package DLTools
 * 
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @license MIT
 * @version v1.0.0
 */
class DLMarkdown {
    /**
     * Instancia de la clase DLMarkdown
     *
     * @var self|null
     */
    private ?self $instance = NULL;

    private function __construct() {
    }

    /**
     * Devuelve una instancia
     *
     * @return self
     */
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Parsea un archivo escrito con la sintaxis de Markdown
     * con la ayuda de una clase y método externo.
     *
     * @param string $view
     * @return string
     */
    public static function parse(string $view): string {
        $root = DLServer::get_document_root();

        $pattern = "/\./";
        $markdown = "";

        $view = trim($view);
        $view = trim($view, ".");
        $view = preg_replace($pattern, DIRECTORY_SEPARATOR, $view);

        /**
         * Obtiene la ruta del archivo Markdown que se va a parsear
         * en documentos HTML.
         * 
         * @var string $filename
         */
        $filename = "{$root}/resources/{$view}.md";

        if (!file_exists($filename)) {
            return $markdown;
        }

        /**
         * Contenido del archivo Markdown previamente seleccionado.
         * 
         * @var string $markdown
         */
        $markdown = (string) file_get_contents($filename) ?? '';
        $markdown = self::stringMarkdown($markdown);

        return $markdown;
    }

    /**
     * Parsea contenido markdown con la ayuda de la clase `GithubFlavoredMarkdownConverter`.
     *
     * @param string $stringMarkdown
     * @return string
     */
    public static function stringMarkdown(string $stringMarkdown): string {
        $exists = class_exists('League\CommonMark\GithubFlavoredMarkdownConverter') &&
            method_exists('League\CommonMark\GithubFlavoredMarkdownConverter', 'convert');

        if ($exists) {
            $converter = new \League\CommonMark\GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_link' => false
            ]);

            $stringMarkdown = $converter->convert($stringMarkdown);
        }

        if (!$exists) {
            $stringMarkdown = "<p>Procesa a instalar la herramienta que falta mediante la siguiente línea:</p>";
            $stringMarkdown .= "<pre style=\"padding: 10px; border-radius: 5px; background-color: #00000050\">composer require league/commonmark</pre>";
        }

        return $stringMarkdown;
    }
}
