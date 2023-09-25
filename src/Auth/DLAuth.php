<?php
namespace DLTools\Auth;

use DLTools\Database\DLDatabase;
use DLTools\HttpRequest\DLHost;
use DLTools\HttpRequest\DLRequest;

/**
 * Permite establecer la autenticación y validación de
 * los usuarios y peticiones.
 * 
 * @package DLTools
 * @license MIT
 * @author David E Luna M <davidlunamontilla@gmail.com>
 */
class DLAuth {
    /**
     * Tiempo de expiración de la sesión del usuario.
     *
     * @var integer
     */
    public int $sessionExpire = 1800;

    /**
     * Token aleatorio que se almacenará en una sesión para prevenir ataques CSRF
     *
     * @var string
     */
    private string $token = "";

    /**
     * Hash de la contraseña a evaluar
     *
     * @var string
     */
    private string $hash = "";

    /**
     * Objecto que permite procesar peticiones
     *
     * @var DLRequest
     */
    private DLRequest $request;

    /**
     * Base de datos.
     *
     * @var DLDatabase
     */
    private DLDatabase $db;

    public function __construct() {
        $this->db = DLDatabase::get_instance();

        $this->set_token();

        $this->request = DLRequest::get_instance();
    }

    /**
     * Se establece un token para proteger el sitio Web
     * para ataques de referencia cruzado.
     *
     * @return void
     */
    private function set_token(): void {
        if (!isset($_SESSION['csrf-token'])) {
            $_SESSION['csrf-token'] = bin2hex(random_bytes(32));
        }

        $this->token = $_SESSION['csrf-token'];
    }

    /**
     * Obtiene el token que se utilizará para comprobar la referencia legítima
     * del usuario.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $token = $auth->get_token();
     * ```
     *
     * @return string
     */
    public function get_token(): string {
        return hash('sha512', $this->token);
    }

    /**
     * Crea la sesión del usuario.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $loggedIn = $auth->login($user);
     * ```
     * 
     * O también de esta forma:
     * 
     * ```
     * $loggedIn = $auth->login($user, [
     *  'g-recaptcha-response' => true
     * ]);
     * ```
     * Si necesita agregar un campo adicional.
     *
     * @param DLUser $user Campos de la tabla usuarios
     * @param array $params Si necesita un campo adicional, lo puede agregar acá.
     * @return boolean
     */
    public function login(DLUser $user, array $params = []): bool {
        /**
         * Nombre de los campos del formulario o petición.
         * 
         * @var object $fieldsName
         */
        $fieldsName = $user->get_credentials_fields();

        /**
         * Valor que indica si el inicio de sesión se ejecutó correctamente.
         */
        $isValid = false;

        /**
         * Datos de la autenticación
         * 
         * @var array $auth
         */
        $auth = [];

        $auth = [
            $fieldsName->usernameField => true,
            $fieldsName->passwordField => true,
            'csrf-token' => true
        ];

        foreach ($params as $key => $value) {
            $auth["$key"] = $value;
        }

        /**
         * Valores obtenidos de los campos del formulario
         * 
         * @var array $values
         */
        $values = $this->request->get_values();

        /**
         * Token de referencia cruzada.
         * 
         * @var string $token
         */
        $token = $values['csrf-token'] ?? '';

        if (($this->request->post($auth)) && ($token === $this->get_token())) {
            $values = $this->request->get_values();

            /**
             * Usuario del sistema.
             * 
             * @var string $username
             */
            $username = $values[$fieldsName->usernameField];

            /**
             * Contraseña del usuario del sistema
             * 
             * @var string $password
             */
            $password = $values[$fieldsName->passwordField];

            $username = strtolower($username);

            /**
             * Datos consultados del usuario.
             * 
             * @var array $data
             */
            $data = $this->db->from($fieldsName->usersTable)
                ->where($fieldsName->usernameField, $username)
                ->first();

            $this->hash = (string) ($data[$fieldsName->passwordField] ?? '');

            /**
             * Valor que indica si la contraseña ingresada por el usuario
             * es válida o no.
             * 
             * @var bool $isValid
             */
            $isValid = $this->checkPassword(($password));

            if (!$isValid) {
                return false;
            }

            /**
             * Token de autenticación almacenado previamente en la base de datos.
             * 
             * @var string $userToken
             */
            $userToken = $data['token'] ?? '';

            if (empty(trim($userToken))) {
                $userToken = $this->get_random_token();
                $this->db->from($fieldsName->usersTable)
                    ->where($fieldsName->usernameField, $username)
                    ->update([
                        'token' => $userToken
                    ]);
            }

            $_SESSION['__INFO__'] = json_encode([
                'user_id' => $data[$fieldsName->IdName] ?? null,
                "token" => $userToken
            ]);

            
            $authAux = [
                'dltools-auth-01' => base64_encode($username),
                'token' => $token,
                'dltools-auth-token' => base64_encode($userToken)
            ];

            $auth = $this->encrypt(json_encode($authAux));
            $this->encryptDecrypt();

            $expire = time() + $this->get_session_expire_time();

            $domain = DLHost::getDomain();
            $secure = DLHost::isHTTPS();

            $session_created = setcookie('_auth_', $auth, $expire, '/', $domain, $secure, true);

            if (!$session_created) {
                return false;
            }
        }

        return $isValid;
    }

    /**
     * Permite crear un usuario nuevo.
     * 
     * Uso:
     * 
     * ```
     * $userCreated = $auth->create_user($user);
     * ```
     *
     * @param DLUser $user
     * @return boolean
     */
    public function create_user(DLUser $user): bool {
        $fieldsName = $user->get_credentials_fields();

        $auth = [
            $fieldsName->usernameField => true,
            $fieldsName->passwordField => true,
            $fieldsName->emailNameField => true
        ];

        if ($this->request->post($auth)) {
            $values = $this->request->get_values();

            $username = $values[$fieldsName->usernameField];
            $password = $values[$fieldsName->passwordField];
            $email = $values[$fieldsName->emailNameField] ?? '';

            $username = strtolower($username);
            $email = strtolower($email);

            $password = $this->setPasswordToken($password);

            $data = $this->db->from($fieldsName->usersTable)
                ->where($fieldsName->usernameField, $username)
                ->where($fieldsName->emailNameField, '=', $email, 'or')
                ->first();

            if (count($data) > 0) {
                return false;
            }

            return $this->db->from($fieldsName->usersTable)
                ->insert([
                    $fieldsName->usernameField => $username,
                    $fieldsName->passwordField => $password,
                    $fieldsName->emailNameField => $email,
                    'token' => $this->get_random_token()
                ]);
        }

        return false;
    }

    /**
     * Crea un nuevo token a partir de una contraseña proporcionada.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $passwordHash = $auth->setPasswordToken('tu contraseña');
     * ```
     *
     * @param string $password
     * @return string
     */
    public function setPasswordToken(string $password): string {
        $options = [
            'memory_cost' => 1 << 17,
            'time_cost' => 4,
            'threads' => 2,
        ];


        return password_hash($password, PASSWORD_ARGON2ID, $options);
    }

    /**
     * Verifica si la contraseña proporcionada por el usuario es
     * correcta:
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $isValidPassword = $auth->checkPassword('tu contraseña');
     * ```
     *
     * Tome en cuenta que la contrase que ingreses aquí se validará
     * con el token previamente creada por medio del método
     * `$this->setPasswordToken(string $password): string`
     * 
     * @param string $password
     * @return boolean
     */
    public function checkPassword(string $password): bool {
        return password_verify($password, $this->hash);
    }

    /**
     * Crea un token y lo almacena en la tabla de sesiones, que es donde
     * es donde tendrá una lista de dispositivos donde tiene la sesión iniciada
     *
     * @param DLUser $user
     * @param integer $time
     * @return object
     */
    private function encryptDecrypt(): object {
        $encrypt_method = "AES-256-CBC";

        /**
         * Longitud del vector de inicialización.
         * 
         * @var int $length
         */
        $length = openssl_cipher_iv_length($encrypt_method);

        /**
         * Tiempo de vida de una cookie
         */
        $time = 24;

        /**
         * Vector de inicialización
         * 
         * @var string
         */
        $iv = '';

        if (isset($_COOKIE['__dlhash__'])) {
            $iv = $_COOKIE['__dlhash__'];
        }

        if (empty($iv)) {
            $iv = openssl_random_pseudo_bytes($length);
        }

        $expire = time() + $this->get_session_expire_time();

        /**
         * Determina si se utiliza el protocolo HTTP Security
         * 
         * @var bool $isSecure
         */
        $isSecure = DLHost::isHTTPS();

        /**
         * Dominio actual del sitio Web.
         * 
         * @var string $domain
         */
        $domain = DLHost::getDomain();

        /**
         * Instancia de la clase encargada de procesar
         * la petición.
         */
        $request = DLRequest::get_instance();

        /**
         * Método de envío de la petición.
         * 
         * @var string $post
         */
        $post = $request->getMethod();

        if (!isset($_COOKIE['__dlhash__'])) {
            setcookie('__dlhash__', $iv, $expire, '/', $domain, $isSecure, true);
        }

        $credentials = $this->db->get_credentials();

        $key = $credentials->DL_KEY ?? 'Una frase secreta aquí';

        return (object) [
            "method" => $encrypt_method,
            "key" => $key,
            "iv" => $iv
        ];
    }

    /**
     * Permite cifrar un texto. Se necesita tres llaves para
     * desencriptarlo. Dos llaves públicas que se almacena en las 
     * cookies y una privada que se almacena en una variable de entorno.
     * 
     * La llave que se genera es aleatoria.
     * 
     * Uso:
     * 
     * ```
     * $encryptContent = $auth->encrypt('Texto a cifrar');
     * ```
     *
     * @param string $text
     * @param integer $time
     * @return string
     */
    public function encrypt(string $text): string {
        $data = $this->encryptDecrypt();
        $output = openssl_encrypt($text, $data->method, $data->key, 0, $data->iv);
        $output = base64_encode($output);

        return $output;
    }

    /**
     * Desencripta el texto cifrato que se pasa como argumento. 
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $text = $auth->decrypt('Tu texto cifrado');
     * ```
     *
     * @param string $hash
     * @return string
     */
    private function decrypt(string $hash): string|false {
        $data = $this->encryptDecrypt();
        return openssl_decrypt(base64_decode($hash), $data->method, $data->key, 0, $data->iv);
    }

    /**
     * Permite ejecutar instrucciones si la sesión del usuario se ha iniciado.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $user = new DLUser;
     * $auth = new DLAuth;
     * 
     * $isAuthenticated = $auth->authenticated($user, function(array $data) {
     *  # Instrucciones ejecutadas aquí si la sesión del usuario 
     *  # se encuentra iniciada.
     * });
     * ```
     *
     * @param DLUser $user
     * @param callable $callback
     * @return boolean
     */
    public function authenticated(DLUser $user, callable $callback): bool {
        $fieldsName = $user->get_credentials_fields();

        $auth = $this->getUserName();
        $auth = $this->decrypt($auth);

        $auth = (array) json_decode($auth);

        /**
         * Nombre del campo de usuario de la tabla usuarios.
         * 
         * @var string $usernameField
         */
        $usernameField = (string) $fieldsName->usernameField;

        /**
         * Nombre del campo de contraseña de la tabla usuarios
         * 
         * @var string $passwordField
         */
        $passwordField = (string) $fieldsName->passwordField;

        $username = '';

        /**
         * Token de autenticación del usuario. Por cada actualización
         * que se haga en el usuario, el token cambiar, lo que cerrará la 
         * sesión automáticamente en todos los dispositivos.
         * 
         * @var string $token
         */
        $token = '';

        if (isset($auth['dltools-auth-01'])) {
            $username = $auth['dltools-auth-01'];
            $username = base64_decode($username);
        }

        if (isset($auth['dltools-auth-token'])) {
            $token = $auth['dltools-auth-token'];
            $token = base64_decode($token);
        }

        if (empty(trim($username))) {
            return false;
        }

        $tableName = $fieldsName->usersTable;

        $data = $this->db->from($tableName)
            ->where($usernameField, $username)
            ->where('token', $token)
            ->first();

        /**
         * Valor de tipo booleano almacenado para determinar si la 
         * contraseña ingresada por el usuario es válida.
         * 
         * @var bool $isValid
         */
        $isValid = false;

        if (!isset($_SESSION['__INFO__'])) {
            return $isValid;
        }

        /**
         * Obtiene los datos de la sesión del usuario y los parsea
         * a un objeto stdClass
         * 
         * @var object sessionInfo
         */
        $sessionInfo = (object) (json_decode($_SESSION['__INFO__']) ?? []);

        $isValid = $sessionInfo->user_id === $data[$fieldsName->IdName] &&
            $sessionInfo->token === $data[$fieldsName->tokenName];

        if (isset($data[$passwordField])) {
            unset($data[$passwordField]);
        }

        if ($isValid) {
            $callback($data);
        }

        /**
         * Regenera el identificador de la sesión cada cinco minutos,
         * pero también cierra la sesión si el tiempo de vida de
         * la sesión ha expirado.
         */
        $this->regenerate_id();

        return $isValid;
    }

    /**
     * Devuelve un JSON codificado en base64
     *
     * @return string
     */
    private function getUserName(): string {
        return $_COOKIE['_auth_'] ?? '';
    }

    /**
     * Permite cerrar la sesión del usuario.
     *
     * @param DLUser $user
     * @return boolean
     */
    public function logout(DLUser $user): bool {
        $delete = false;

        $fieldsName = $user->get_credentials_fields();
        $logoutField = $fieldsName->logoutField;

        $auth = [
            $logoutField => true,
            'csrf-token' => true
        ];

        if (!($this->request->post($auth))) {
            return false;
        }


        $expire = time() - 7200;

        $values = $this->request->get_values();

        $isValid = $values['csrf-token'] === $this->get_token() &&
            $values[$logoutField] === 'logout';

        if ($isValid) {
            $delete = setcookie('_dl0', '', $expire, '/', DLHost::getDomain(), true) &&
                setcookie('_dl1', '', $expire, '/', DLHost::getDomain(), true) &&
                setcookie('_auth_', '', $expire, '/', DLHost::getDomain(), true);
        }

        return $delete && $this->session_destroy();
    }

    /**
     * Devuelve un token aleatorio.
     *
     * @return string
     */
    public function get_random_token(): string {
        /**
         * Logitud del token en bytes
         * 
         * @var int $length
         */
        $length = 150;

        /**
         * 150 bytes en forma de cadena caracteres.
         * 
         * @var string $randomBytes
         */
        $randomBytes = random_bytes($length);

        /**
         * Datos aleatorios convertidos en una cadena hexadecimal.
         * 
         * @var string $randomString
         */
        $randomString = bin2hex($randomBytes);

        return substr($randomString, 0, 300);
    }

    /**
     * Regenera cada 5 minutos el identificador de las sesiones.
     *
     * @return void
     */
    public function regenerate_id(): void {
        /**
         * Último acceso.
         * 
         * @var int $lastAccess
         */
        $lastAccess = time();

        if (!isset($_SESSION['last-access'])) {
            $_SESSION['last-access'] = $lastAccess;
        }

        if (isset($_SESSION['last-access'])) {
            $lastAccess = (int) ($_SESSION['last-access'] ?? 0);
        }

        /**
         * Tiempo inactivo.
         * 
         * @var int $idleTime
         */
        $idleTime = time() - $lastAccess;

        if ($idleTime >= $this->get_session_expire_time()) {
            session_unset();
            session_destroy();
        }

        if ($idleTime >= 5 * 60) {
            session_regenerate_id(true);
        }
    }

    /**
     * Establece el tiempo de expiración de la sesión
     *
     * @param integer $time Tiempo en segundo del tiempo de expiración
     * @return void
     */
    public function set_session_expire_time(int $time): void {
        $this->sessionExpire = (int) $time;
    }

    /**
     * Devuelve el tiempo de expiración de la sesión. El 
     * tiempo devuelto está expresado en segundos.
     *
     * @return integer
     */
    public function get_session_expire_time(): int {
        return (int) $this->sessionExpire;
    }

    /**
     * Destruye las cookie de sesión.
     * 
     * @return bool
     */
    public function session_destroy(): bool {
        $unset = session_unset();
        $destroy = session_destroy();

        return $unset && $destroy;
    }
}
