<?php

namespace DLTools\Auth;

use DLTools\Config\DLConfig;
use DLTools\HttpRequest\DLRequest;

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
        $config = DLConfig::getInstance();
        $request = DLRequest::getInstance();

        /**
         * Respuesta recibida de Google.
         * 
         * @var string $response
         */
        $response = ($request->getValues())['g-recaptcha-response'];

        $credentials = $config->getCredentials();

        // Ruta de la petición:
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $ip = @$_SERVER['REMOTE_ADDR'];

        // Datos de envío:
        $datos = [
            "secret" => $credentials->G_SECRET_KEY ?? '',
            "response" => $response,
            "remoteip" => $ip
        ];

        // Opciones de envío:
        $opciones = [
            "http" => [
                "header" => "Content-type: application/x-www-form-urlencoded\r\n",
                "method" => "POST",
                "content" => http_build_query($datos)
            ]
        ];

        // Preparando la petición:
        $contexto = stream_context_create($opciones);

        // Enviar la petición:
        $resultados = file_get_contents($url, false, $contexto);
        $resultados = json_decode($resultados);


        return $resultados->success;
    }

    /**
     * Devuelve una instancia única de la clase DLRecaptcha
     *
     * @return self
     */
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
