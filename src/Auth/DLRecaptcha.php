<?php

namespace DLTools\Auth;

use DLRoute\Requests\DLRequest;
use DLRoute\Server\DLServer;
use DLTools\Config\DLConfig;

/**
 * Por ahora, procesa el reCAPTCHA creado por Google. En futuras
 * versiones adoptará otras reCAPTCHAS.
 * 
 * @package DLTools
 * 
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @version v1.0.0
 * @license MIT
 */
class DLRecaptcha {

    use DLConfig;
    
    private static ?self $instance = NULL;

    private function __construct() {
    }

    /**
     * Envía una petición a Google con los datos recibidos del
     * usuario y verifica si es o no un SPAM.
     *
     * @return boolean
     */
    public function post(): bool {
        $this->parse_file();
        
        /**
         * Instancia del procesador de peticiones
         * 
         * @var DLRequest $request
         */
        $request = DLRequest::get_instance();

        /**
         * Respuesta recibida de Google.
         * 
         * @var string $response
         */
        $response = ($request->get_values())['g-recaptcha-response'] ?? null;

        if (is_null($response)) {
            throw new \Error("Para validar con Google, utilice el campo `g-recaptcha-response`");
        }

        /**
         * Credenciales devueltas de las variables de entorno
         * 
         * @var object $credentials
         */
        $credentials = $this->get_environments_as_object();

        /**
         * Ruta de la petición
         * 
         * @var string $url
         */
        $url = "https://www.google.com/recaptcha/api/siteverify";

        /**
         * Dirección IP del cliente HTTP.
         * 
         * @var string $ip
         */
        $ip = DLServer::get_ipaddress();

        if (!isset($credentials->G_SECRET_KEY)) {
            return false;
        }

        if (!array_key_exists('value', $credentials->G_SECRET_KEY)) {
            return false;
        }

        /**
         * Clave secreta de Google
         * 
         * @var string $secret_key
         */
        $secret_key = $credentials->G_SECRET_KEY['value'];

        /**
         * Parámetros de la petición
         * 
         * @var array $datos
         */
        $datos = [
            "secret" => $secret_key,
            "response" => $response,
            "remoteip" => $ip
        ];

        /**
         * Cabecera HTTP
         * 
         * @var array $opciones
         */
        $opciones = [
            "http" => [
                "header" => "Content-type: application/x-www-form-urlencoded\r\n",
                "method" => "POST",
                "content" => http_build_query($datos)
            ]
        ];

        /**
         * Contexto de la petición
         * 
         * @var resource $contexto
         */
        $contexto = stream_context_create($opciones);

        /**
         * Resultados de la petición
         * 
         * @var string|array|object $resultados
         */
        $resultados = file_get_contents($url, false, $contexto);
        $resultados = json_decode($resultados);


        return $resultados->success;
    }

    /**
     * Devuelve una instancia única de la clase DLRecaptcha
     *
     * @return self
     */
    public static function get_instance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
