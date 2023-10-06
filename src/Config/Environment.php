<?php

namespace DLTools\Config;

use TypeError;

/**
 * Carga todas las variables de entorno
 * 
 * @package DLTools\Config;
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
final class Environment {
    use DLConfig;

    /**
     * Instancia de clase
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Credenciales críticas de las variables de entorno
     *
     * @var Credentials|null
     */
    private ?Credentials $credentials = null;

    public function __construct() {
        $this->parse_file();

        /**
         * Credenciales como objeto
         * 
         * @var object $environment
         */
        $environment = $this->get_environments_as_object();

        $this->credentials = Credentials::get_instance(
            $environment
        );
    }

    /**
     * Devuelve una instanciade clase
     *
     * @return self
     */
    public static function get_instance(): self {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Devuelve las credenciales de las variables de entorno
     *
     * @return Credentials
     * 
     * @throws TypeError
     */
    public function get_credentials(): Credentials {
        if (!($this->credentials instanceof Credentials)) {
            throw new TypeError("Debes instanciar `Envorinment`");
        }

        return $this->credentials;
    }
}