<?php

namespace DLTools\Auth;

use DLRoute\Requests\DLOutput;
use DLRoute\Requests\DLRoute;
use DLRoute\Routes\RouteDebugger;
use DLRoute\Server\DLServer;
use DLTools\Config\Credentials;
use DLTools\Config\DLConfig;
use DLTools\Config\Logs;
use DLTools\Interfaces\AuthInterface;
use Error;

class DLAuth implements AuthInterface {

    use DLConfig;

    /**
     * Instancia de clase
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Token de validación de referencia
     *
     * @var string
     */
    private string $token = "";

    /**
     * Nombre de la tabla a ser consultada para comprobar los datos de la sesión.
     *
     * @var string
     */
    private string $table = "dl_users";

    private function __construct() {
    }

    /**
     * Devuelve una instance de clase
     *
     * @return self
     */
    public static function get_instance(): self {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function get_token(): string {
        $this->set_token('csrf-token');
        return $this->token;
    }

    public function get_hash(): string {
        /**
         * Bytes en formato binario.
         * 
         * @var string
         */
        $bytes = random_bytes(512);

        /**
         * Bytes en formato hexadecimal.
         * 
         * @var string
         */
        $random_string = bin2hex($bytes);

        return $random_string;
    }

    public function auth(DLUser $user, array | DLAuthOptions $options = [], ?DLCookie $cookie = null): bool {
        /**
         * Variables de entorno.
         * 
         * @var Credentials
         */
        $credentiales = $this->get_credentials();

        /**
         * Nombre del campo del nombre de usuario de la tabla de usuarios.
         * 
         * @var string $username_field
         */
        $username_field = 'username';

        /**
         * Nombre del campo de la contraseña de la tabla de usuarios.
         * 
         * @var string $password_field
         */
        $password_field = 'password';

        /**
         * Nombre del campo de token de la tabla de usuarios. Útil para
         * permitir cerrar sesión en todos los dispositivos al mismo tiempo.
         * 
         * @var string $token_field
         */
        $token_field = 'token';


        if ($options instanceof DLAuthOptions) {
            $username_field = $options->get_username_field();
            $password_field = $options->get_password_field();
            $token_field = $options->get_token_field();
        } elseif (is_array($options)) {
            $username_field = $options['username_field'] ?? $username_field;
            $password_field = $options['password_field'] ?? $password_field;
            $token_field = $options['token_field'] ?? $token_field;
        }

        /**
         * No continúa si algunos de los tres campos de la tabla de usuarios
         * no se ha definido.
         * 
         * @var boolean
         */
        $is_null = is_null($username_field) ||
            is_null($password_field) ||
            is_null($token_field);

        if ($is_null) {
            header("Content-Type: application/json; charset=utf-8", true, 500);

            /**
             * Detalles de errores.
             * 
             * @var array
             */
            $error = [
                "status" => false,
                "error" => 'Los campos no deben ser nulos',
                "details" => [
                    "username_field" => $username_field,
                    "password_field" => $password_field,
                    "token_field" => $token_field
                ]
            ];

            if ($credentiales->is_production()) {
                echo DLOutput::get_json([
                    "status" => false,
                    "error" => "Error 500"
                ], true);

                Logs::save('username.log', $error);
                return false;
            }

            echo DLOutput::get_json($error, true);
            return false;
        }

        /**
         * Datos del usuario con el se va a autenticar.
         * 
         * @var array
         */
        $user_data = $user->where($username_field, $user->get_username())->first();

        /**
         * Token de autenticación de usuario.
         * 
         * @var string $user_token
         */
        $user_token = $user_data[$token_field] ?? $this->generate_token();


        if (array_key_exists($password_field, $user_data)) {
            $user->set_password_hash(
                $user_data[$password_field]
            );
        }

        if (array_key_exists($token_field, $user_data)) {
            $user->set_token_user(
                $user_token
            );

            $user->where($username_field, $user->get_username())->update([
                $token_field => $user_token
            ]);
        }

        /**
         * Token de autenticación inicial
         * 
         * @var string
         */
        $token = $user->get_token();

        /**
         * Nombre de usuario
         * 
         * @var string|null
         */
        $username = $user->get_username();

        /**
         * Hash de la contraseña de usuario
         * 
         * @var string|null
         */
        $password_hash = $user->get_password_hash();

        /**
         * Contraseña de usuario.
         * 
         * @var string
         */
        $password = $user->get_password();

        $is_valid = password_verify($password, $password_hash);

        /**
         * Datos que se usarán para consultar los datos en la base de datos.
         * 
         * @var array<string, string> | null
         */
        $auth = null;

        if ($is_valid) {

            if (is_string($token)) {
                $token = trim($token);
            }

            if (is_null($token) || empty($token)) {
                $token = $this->generate_token();

                /**
                 * Si el token de autenticación no se encuentra definido previamente, entonces, 
                 * se generará de forma automáticamente.
                 * 
                 * Este token se utilizará para cerrar sesión en múltiples dispositivos.
                 */
                $user->where($username_field, $username)->update([
                    $token_field => $token
                ]);

                if (array_key_exists($token_field, $user_data)) {
                    $user_data[$token_field] = $token;
                }
            }

            if (array_key_exists($password_field, $user_data)) {
                unset($user_data[$password_field]);
            }

            $auth = array_merge($user_data, [
                "ip" => DLServer::get_ipaddress(),
                "user_agent" => DLServer::get_user_agent(),
                "hostname" => DLServer::get_hostname(),
                "http_host" => DLServer::get_http_host(),
                "server_software" => DLServer::get_server_software(),
                "port" => DLServer::get_port(),
                $token_field => $user_token
            ]);

            /**
             * Credenciales tomadas de la variable de entorno
             * 
             * @var Credentials
             */
            $credentiales = $this->get_credentials();

            /**
             * Establece los bytes en formato binario
             * 
             * @var string $bytes
             */
            $bytes = random_bytes(128);


            /**
             * Token de validación de sesiones
             * 
             * @var string $token
             */
            $token = bin2hex($bytes);



            if ($cookie instanceof DLCookie) {
                $cookie->set_name('__auth__');
                $cookie->set_value($token);

                $cookie->create_cookie();
            } else {
                setcookie(
                    "__auth__",
                    $token,
                    time() + 60 * 60 * 24 * 30,
                    "/",
                    DLServer::get_hostname(),
                    $credentiales->is_production(),
                    true
                );
            }

            $_SESSION['__auth__'] = $token;
        }

        $_SESSION['auth'] = $auth;

        return $is_valid;
    }

    public function logged(callable $callback): void {
        $logged = $this->is_logged();
        $this->restrict_route($callback, $logged, 403);
    }

    public function not_logged(callable $callback): void {
        $logged = $this->is_logged();
        $this->restrict_route($callback, !$logged, 403);
    }

    /**
     * Vacía los datos de la sesión.
     *
     * @return void
     */
    public function clear_auth(): void {
        $_SESSION['auth'] = null;
    }

    /**
     * Establece el token de referencia.
     *
     * @param string $field Nombre del token
     * @return void
     */
    protected function set_token(string $field): void {
        $hash = $this->get_hash();

        $this->set_session_value($field, $hash);
        $this->token = $this->get_session_value($field);
    }

    /**
     * Crea y establece una variable de sesión
     *
     * @param string $field Campo
     * @param mixed $value Valor
     * @return void
     */
    protected function set_session_value(string $field, mixed $value): void {

        if (!array_key_exists($field, $_SESSION) || empty($_SESSION[$field])) {
            $_SESSION[$field] = $value;
        }
    }

    /**
     * Devuelve un valor almacenado previamente en la variable de sesión.
     *
     * @param string $field Campo o clave de la variable de sesión.
     * @return mixed
     */
    public function get_session_value(string $field): mixed {
        /**
         * Valor de una variable de sesión.
         * 
         * @var string
         */
        $value = null;

        if (array_key_exists($field, $_SESSION)) {
            $value = $_SESSION[$field];

            if (is_string($value)) {
                $value = trim($value);
            }

            if (empty($value)) {
                $value = null;
            }
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        return $value;
    }

    /**
     * Devuelve el token del usuario
     *
     * @return array
     */
    public function get_auth(): array {
        /**
         * Devuelve un token de usuario.
         * 
         * @var array
         */
        $auth = $this->get_session_value('auth');

        return is_array($auth)
            ? $auth
            : [];
    }

    /**
     * Genera un token en formato hexadecimal con `1535` caracteres.
     *
     * @return string
     */
    private function generate_token(): string {
        /**
         * Bytes en formato binario.
         * 
         * @var string
         */
        $bytes = random_bytes(512);

        /**
         * Bytes en formato hexadecimal.
         * 
         * @var string
         */
        $hex = bin2hex($bytes);

        preg_match_all("/[0-9a-f]{2}/i", $hex, $matches);

        return implode(" ", $matches[0]);
    }

    /**
     * Verifica si el usuario ha iniciado sesión en la aplicación.
     *
     * @return bool Devuelve `true` si el usuario ha iniciado sesión; de lo contrario, devuelve `false`.
     */
    protected function is_logged(): bool {
        /**
         * Datos de autenticación del usuario
         * 
         * @var array $auth
         */
        $auth = $this->get_auth();

        return count($auth) > 0;
    }

    /**
     * Devuelve un mensaje con código de estado.
     *
     * Indica que se requiere autenticación para acceder a una ruta y devuelve un mensaje con el código de estado especificado.
     *
     * @param string $message El mensaje que describe la necesidad de autenticación.
     * @param int $code (Opcional) El código de estado HTTP a utilizar (por defecto es 401 para "No autorizado").
     * @return void
     */
    protected function required_authentication(string $message, int $code = 401): void {

        if ($code !== 401 && $code !== 403) {
            throw new Error("Solo se permiten los códigos de estados 401 y 403", 500);
        }

        header("Content-Type: application/json; charset=utf-8", true, $code);

        /**
         * Mensaje personalizado de error.
         * 
         * @var array $error
         */
        $error = [
            "code" => $code,
            "error" => $message
        ];

        echo DLOutput::get_json($error, true);
        exit;
    }

    /**
     * Permite permite restringir las rutas o no.
     *
     * @param callable $callback Registra las rutas en los métodos de autenticación
     * @param boolean $allow Indica si se desea permitir o no la ruta previamente registrada
     * @param integer|null $code Código de estado
     * @param string|null $redirect_to Redirige a una ruta específica
     * @return void
     */
    protected function restrict_route(callable $callback, bool $allow = true, ?int $code = null, ?string $redirect_to = null): void {
        if ($code !== 401 && $code !== 403 && $code !== 301 && $code !== 302) {
            throw new Error("Solo se permiten los valores numéricos 401, 403, 301 y 302", 500);
        }

        /**
         * Indica si la ruta y método HTTP existen previamente
         * 
         * @var boolean $exists
         */
        $before_exists = $this->route_exists();

        $callback();

        /**
         * Indica si la ruta y método actual se registraron al momendo de ejecutar `$callback`.
         * 
         * @var boolean $after_exists
         */
        $after_exists = $this->route_exists();

        /**
         * Indica si la ruta es nueva
         * 
         * @var boolean $is_new
         */
        $is_new = !$before_exists && $after_exists;

        /**
         * @var string $route
         */
        $route = DLServer::get_route();

        /**
         * @var string $method
         */
        $method = DLServer::get_method();

        /**
         * Nombre del método estático relacionado al método HTTP
         * 
         * @var string $method_name
         */
        $method_name = strtolower($method);
        $method_name = trim($method_name);

        /**
         * Verifica si el método de la clase `DLRoute` existe
         * 
         * @var boolean $method_exists
         */
        $method_exists = method_exists(DLRoute::class, $method);

        if (!$method_exists) {
            return;
        }


        if ($is_new && !$allow) {
            if (!is_null($redirect_to)) {
                $redirect_to = RouteDebugger::trim_slash($redirect_to);

                /**
                 * Ruta base HTTP.
                 * 
                 * @var string $http
                 */
                $http = DLServer::get_base_url();
                $http = RouteDebugger::trim_slash($http);
                /**
                 * URL completa de la aplicación
                 * 
                 * @var string $route
                 */
                $url = "{$http}/{$redirect_to}";

                header("Location: {$url}", true, $code);
            }

            header("Content-Type: application/json; charset=utf-8", true, $code);

            DLRoute::{$method_name}(
                "{$route}",
                [Unauthorized::class, $code === 401 ? 'unauthorized' : 'forbidden']
            );
        }
    }

    /**
     * Verifica si la ruta de la aplicación ya existe
     * 
     * @return boolean
     */
    private function route_exists(): bool {
        /**
         * Métodos y rutas registrados
         * 
         * @var array $routes
         */
        $routes = DLRoute::get_routes();

        /**
         * Método HTTP
         * 
         * @var string $method
         */
        $method = DLServer::get_method();

        /**
         * Ruta HTTP
         * 
         * @var string $route
         */
        $route = DLServer::get_route();


        if (!array_key_exists($method, $routes)) {
            return false;
        }

        if (!array_key_exists($route, $routes[$method])) {
            return false;
        }

        return true;
    }
}
