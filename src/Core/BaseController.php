<?php

declare(strict_types=1);

namespace DLTools\Core;

use Core\Request\Request;
use DLRoute\Config\Controller;
use DLRoute\Requests\DLOutput;
use DLRoute\Server\DLServer;
use DLTools\Auth\DLCookie;
use DLTools\Core\Errors\ForbiddenException;
use DLTools\Core\Traits\Token;
use Exception;

abstract class BaseController extends Controller {
    use Token;

    /**
     * Almacena los valores de la petición
     *
     * @var array
     */
    private array $values = [];

    /**
     * Contenido en bruto de un cliente HTTP
     *
     * @var string
     */
    private string $content = "";

    /**
     * Petición del cliente HTTP
     *
     * @var Request|null $http
     */
    protected ?Request $http;

    public function __construct() {
        $this->http = Request::get_instance();
        $this->send_csrf_token();
        parent::__construct();
    }

    /**
     * Devuelve tokens aleatorio para evitar ejecución no atorizada de scripts
     *
     * @return string
     */
    protected function get_random_token(): string {
        /**
         * Bytes aleatorio
         * 
         * @var string $bytes
         */
        $bytes = random_bytes(36);

        /**
         * Token aleatorios
         * 
         * @var string $token
         */
        $token = bin2hex($bytes);

        return $token;
    }

    /**
     * Obtiene el contenido en bruto del cuerpo de la solicitud HTTP.
     * 
     * Este método lee los datos directamente desde `php://input`, que proporciona
     * el flujo de entrada sin procesar enviado por el cliente. Es útil para
     * manejar peticiones con datos en formatos como JSON, XML o formularios
     * enviados con `application/x-www-form-urlencoded` o `multipart/form-data`.
     *
     * @return string El contenido en bruto de la solicitud HTTP.
     */
    protected function get_content(): string {
        /**
         * Contenido en bruto de la solicitud HTTP.
         *
         * @var string
         */
        $content = @file_get_contents('php://input');

        return $content;
    }


    /**
     * Devuelve en un array asociativo los valores de la petición
     *
     * @return array
     */
    protected function get_values(): array {
        /**
         * Contenido de un cliente HTTP
         * 
         * @var string|array
         */
        $content = $this->http->get_values();

        return is_array($content) ? $content : [];
    }

    /**
     * Devuelve una dirección de correo. Si el correo enviado por el cliente
     * HTTP es inválido devolverá un error.
     *
     * @param string $field
     * @return string
     */
    protected function get_email(string $field): string {
        return $this->http->get_email($field);
    }

    /**
     * Obtiene un UUID (Identificador Único Universal) para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener el UUID.
     * @return string El UUID generado para el campo.
     */
    protected function get_uuid(string $field): string {
        return $this->http->get_uuid($field);
    }


    /**
     * Cifra una contraseña utilizando el algoritmo `Argon2id` con opciones personalizadas.
     * Esta función toma una contraseña en forma de cadena de texto y utiliza el algoritmo Argon2id para generar un hash seguro.
     *
     * @param string $field El nombre del campo para el que se desea obtener la contraseña.
     * @return string La contraseña válida generada para el campo.
     */
    protected function get_password(string $field): string {
        $password = $this->http->get_password_valid($field);
        return $this->get_password_hash($password);
    }


    /**
     * Obtiene un valor flotante para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener el valor flotante.
     * @return float El valor flotante obtenido para el campo.
     */
    protected function get_float(string $field): float {
        return $this->http->get_float($field);
    }


    /**
     * Obtiene un valor entero para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener el valor entero.
     * @return int El valor entero obtenido para el campo.
     */
    protected function get_integer(string $field): int {
        return $this->http->get_integer($field);
    }

    /**
     * Obtiene un valor numérico para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener el valor numérico.
     * @return float | int El valor numérico obtenido para el campo, que puede ser un entero o un número de punto flotante.
     */
    protected function get_numeric(string $field): float|int {
        return $this->http->get_numeric($field);
    }

    /**
     * Obtiene una cadena de texto para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener la cadena de texto.
     * @return string La cadena de texto obtenida para el campo.
     */
    protected function get_string(string $field): string {
        return $this->http->get_string($field);
    }

    /**
     * Obtiene una entrada de usuario para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener la entrada de usuario.
     * @return string La entrada de usuario obtenida para el campo.
     */
    protected function get_input(string $field): string|null {
        return $this->http->get_input($field);
    }

    /**
     * Devuelve un array a partir de la entrada de un cliente HTTP
     *
     * @param string $key Clave o propiedad del objeto `JSON` enviado
     * @return array
     */
    protected function get_array(string $field): array {
        $exists = $this->http->get_input($field);
        return !is_null($exists) ? $this->http->get_array($field) : [];
    }

    /**
     * Obtiene un valor requerido para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener el valor requerido.
     * @return string El valor requerido obtenido para el campo.
     */
    protected function get_required(string $field): string {
        return $this->http->get_required($field);
    }

    /**
     * Obtiene un valor booleano para el campo especificado.
     *
     * @param string $field El nombre del campo para el que se desea obtener el valor booleano.
     * @return bool El valor booleano obtenido para el campo.
     */
    protected function get_boolean(string $field): bool {
        return $this->http->get_boolean($field);
    }

    /**
     * Método para enviar un token `CSRF (Cross-Site Request Forgery)` como encabezado `HTTP`.
     * Este token se utiliza para proteger contra ataques de referencia cruzada.
     *
     * @param int $lifetime Opcional. Establece el tiempo de vida del token `CSRF`. El valor por defecto es `DLCookie::LIFETIME_DAY`
     * @param bool $secure Opcional. Indica si la cookie debe ser enviada a traves de conexiones `HTTPS` únicamente.
     * @return void
     */
    private function send_csrf_token(int $lifetime = DLCookie::LIFETIME_DAY, bool $secure = false): void {
        /**
         * Token CSRF
         *
         * Este token se genera para proteger las solicitudes contra ataques de referencia cruzada.
         *
         * @var string $token
         */
        $token = $this->get_csrf_token();

        $cookie = new DLCookie();
        $cookie->set_name('__csrf');
        $cookie->set_value(value: $token);
        $cookie->set_lifetime($lifetime);
        $cookie->set_path('/');
        $cookie->set_domain(DLServer::get_hostname());
        $cookie->set_secure(secure: $secure);
        $cookie->set_http_only(http_only: true);
    }

    /**
     * Validar un token CSRF en una solicitud HTTP.
     *
     * Esta función verifica si el token CSRF enviado por el cliente coincide con el token CSRF
     * almacenado en la sesión actual. Si no coinciden, se genera un mensaje de error y se
     * devuelve una respuesta HTTP 403 Forbidden para indicar que la solicitud ha sido rechazada
     * por razones de seguridad.
     *
     * @return void
     */
    protected function validate_csrf_token(): void {
        /**
         * Mensaje de error en caso de token CSRF inválido.
         *
         * @var string $error
         */
        $error = "Token CSRF inválido. La solicitud ha sido rechazada por motivos de seguridad.";

        /**
         * Token enviado por el cliente HTTP.
         *
         * @var string $client_token
         */
        $client_token = $_COOKIE['__csrf'] ?? null;

        /**
         * Token CSRF almacenado en la sesión actual.
         *
         * @var string $token
         */
        $token = $this->get_csrf_token();

        if (is_null($client_token)) {
            throw new ForbiddenException($error);
        }

        if ($token !== $client_token) {
            throw new ForbiddenException($error);
        }

        $_SESSION['csrf_token'] = null;
    }

    /**
     * Devuelve una descripción depurada para un Meta Description
     *
     * @param string $input Entrada a ser depurada
     * @param integer $length Longitud máxima de caracteres permitidas
     * @return string
     * 
     * @throws Exception
     */
    protected function get_description(string $input, int $length = 160): string {
        $input = strip_tags($input);

        if ($length < 1) {
            throw new Exception("Debe permitirse, al menos, 1 carácter", 500);
        }

        /**
         * Obtiene la longitud actual de caracteres
         * 
         * @var integer $current_length
         */
        $current_length = strlen($input);

        $input = preg_replace("/\s+/", ' ', $input);
        $input = substr($input, 0, $length);
        $input = trim($input);

        if ($current_length > $length) {
            $input .= "...";
        }

        return $input;
    }

    /**
     * Establece una restricción para permitir solamente, peticiones Ajax o Fetch
     *
     * @param array|null $data Datos a presentar
     * @return void
     * 
     * @throws ForbiddenException
     */
    protected function only_fetch(?array $data = null): void {
        if (!array_key_exists('HTTP_REFERER', $_SERVER) && is_null($data)) {
            throw new ForbiddenException('Solo se permitea peticiones Ajax o Fetch', 403);
        }

        if (!array_key_exists('HTTP_REFERER', $_SERVER) && !is_null($data)) {
            header("content-type: application/json; charset=utf-8", true, 200);
            echo DLOutput::get_json($data, true);
            exit;
        }
    }
}
