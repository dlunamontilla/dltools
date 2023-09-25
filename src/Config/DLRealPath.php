<?php

namespace DLTools\Config;

class DLRealPath {

    /**
     * Ruta raíz del proyecto.
     *
     * @var string
     */
    private string $documentRoot;

    private static ?self $instance = null;

    private function __construct() {
        $this->setPath();
    }

    /**
     * Devuelve la instancia de RealPath
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
     * Devuelve la ruta raíz del proyecto
     *
     * @return string
     */
    public function getDocumentRoot(): string {
        return $this->documentRoot;
    }

    /**
     * Establece la ruta raíz del proyecto.
     *
     * @return void
     */
    private function setPath(): void {
        if (defined('DOCUMENT_ROOT')) {
            $this->documentRoot = DOCUMENT_ROOT;
            return;
        }

        $this->documentRoot = realpath(dirname(getcwd(), 1));
    }

    /**
     * Devuelve el directorio real de trabajo
     *
     * @return string
     */
    public function getWorkDir(): string {
        $workdir = $_SERVER['SCRIPT_NAME'];
        $workdir = basename(dirname($workdir, 1));

        return $workdir;
    }

    /**
     * Devuelve la URI del directorio de trabajo.
     *
     * @return string
     */
    public function getURIFromWordDir(): string {
        $self = $_SERVER['SCRIPT_NAME'];
        $uri = dirname($self, 1);
        return $uri;
    }
}