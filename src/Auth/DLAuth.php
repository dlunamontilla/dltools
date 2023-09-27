<?php
namespace DLTools\Auth;

use DLRoute\Requests\DLOutput;
use DLTools\Config\Credentials;
use DLTools\Config\DLConfig;
use DLTools\Config\Logs;
use DLTools\Interfaces\AuthInterface;


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

    public function get_username(): string {
        /**
         * Nombre de usuario.
         * 
         * @var string
         */
        $username = $this->get_session_value('user_username');

        return trim($username);
    }

    public function get_name(): string {
        /**
         * Nombres del usuario
         * 
         * @var string
         */
        $name = $this->get_session_value('user_name');

        return trim($name);
    }

    public function get_lastname(): string {
        /**
         * Apellidos del usuario
         * 
         * @var string
         */
        $lastname = $this->get_session_value('user_lastname');

        return trim($lastname);
    }

    public function get_user_uuid(): string {
        $uuid = $this->get_session_value('user_uuid');
        return trim($uuid);
    }

    public function get_user_id(): int {
        $id = (int) $this->get_session_value('user_id');
        return $id;
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

    /**
     * Permite autenticar el usuario
     *
     * @param DLUser $user Usuario a autenticar
     * @param array $options Opciones de autenticación
     * @return void
     */
    public function auth(DLUser $user, $options = []): void {
        /**
         * Variables de entorno.
         * 
         * @var Credentials
         */
        $credentiales = $this->get_credentials();

        /**
         * Nombre del campo del nombre de usuario de la tabla de usuarios.
         * 
         * @var string|null
         */
        $username_field = null;

        /**
         * Nombre del campo de la contraseña de la tabla de usuarios.
         * 
         * @var string|null
         */
        $password_field = null;

        /**
         * Nombre del campo de token de la tabla de usuarios. Útil para
         * permitir cerrar sesión en todos los dispositivos al mismo tiempo.
         * 
         * @var string|null
         */
        $token_field = null;

        if (array_key_exists('username_field', $options)) {
            $username_field = $options['username_field'];
        }

        if (array_key_exists('password_field', $options)) {
            $password_field = $options['password_field'];
        }

        if (array_key_exists('token_field', $options)) {
            $token_field = $options['token_field'];
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
                return;
            }

            echo DLOutput::get_json($error, true);
            return;
        }

        /**
         * Datos del usuario con el se va a autenticar.
         * 
         * @var array
         */
        $user_data = $user->where(
            $username_field,
            $user->get_username()
        )->first();

        if (array_key_exists($password_field, $user_data)) {
            $user->set_password_hash(
                $user_data[$password_field]
            );
        }

        if (array_key_exists($token_field, $user_data)) {
            $user->set_token_user(
                $user_data[$token_field]
            );
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

            $auth = $user_data;
        }

        $this->set_session_value('auth', $auth);
    }

    public function logged(callable $callback): void {
        /**
         * Datos de autenticación de sesiones.
         * 
         * @var string
         */
        $auth = $this->get_auth();

        if (!empty($auth)) {
            $callback();
        }
    }

    public function not_logged(callable $callback): void {
        /**
         * Si el usuario no está autenticado, entonces, el token será
         * nulo.
         * 
         * @var array
         */
        $auth = $this->get_auth();

        if (empty($auth)) {
            $callback();
        }
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
    private function set_token(string $field): void {
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
    private function set_session_value(string $field, mixed $value): void {

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
    private function get_session_value(string $field): mixed {
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
    private function get_auth(): array {
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


}
