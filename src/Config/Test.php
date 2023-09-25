<?php

namespace DLRoute\Config;

use DLRoute\Requests\DLOutput;
use DLTools\Config\DLEnvironment;

final class Test {

    use DLEnvironment;

    private static ?self $instance = null;

    private function __construct() {
        $this->parse_file();
        
        /**
         * Variables de entorno
         * 
         * @var object $vars
         */
        $vars = $this->get_environments_as_object();

        echo PHP_EOL . DLOutput::get_json($vars, true);
    }

    /**
     * Devuelve una instancia de clase.
     *
     * @return self
     */
    public static function get_instance(): self {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}