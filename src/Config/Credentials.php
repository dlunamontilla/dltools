<?php

namespace DLTools\Config;

use DLRoute\Requests\DLOutput;
use DLTools\Interfaces\CredentialsInterface;

/**
 * Datos críticos de autenticación.
 * 
 * @package DLTools\Config
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
final class Credentials implements CredentialsInterface {

    /**
     * Instancia de clase
     *
     * @var self|null
     */
    private static ?self $instance = null;
    /**
     * Indica si la aplicación debe correr en modo producción o desarrollo. Si vale
     * `true` la aplicación debe correr en modo producción.
     *
     * @var boolean $production
     */
    private bool $production = false;

    /**
     * Servidor de base de datos.
     *
     * @var string $host
     */
    private string $host = "localhost";

    /**
     * Puerto del motor de base de datos
     *
     * @var integer $port
     */
    private int $port = 3306;

    /**
     * Usuario del motor de base de datos
     *
     * @var string $username
     */
    private string $username = "root";

    /**
     * Contraseña de la base de datos.
     *
     * @var string $password
     */
    private string $password = "";

    /**
     * Nombre de la base de datos
     *
     * @var string|null $database
     */
    private string $database = "";

    /**
     * Codificación de caracteres. Valor predeterminado es `utf8`
     *
     * @var string $charset
     */
    private string $charset = "utf8";

    /**
     * @var string $collation
     */
    private string $collation = "utf8_general_ci";

    /**
     * Motor de base de datos. Valor por defecto: `mysql`
     *
     * @var string $drive
     */
    private string $drive = "mysql";

    /**
     * Prefijo de tabla de la base de datos.
     *
     * @var string $prefix
     */
    private string $prefix = "";

    /**
     * Servidor de correo SMTP
     *
     * @var string
     */
    private string $mail_host = "smtp.example.com";

    /**
     * Correo electrónico que se usará para enviar correos desde la Web.
     *
     * @var string
     */
    private string $mail_username = "no-reply@tu-dominio.com";

    /**
     * Contraseña del correo electrónico que enviará correo desde la Web
     *
     * @var string
     */
    private string $mail_password = "";

    /**
     * Puerto SSL del servidor SMTP
     *
     * @var integer
     */
    private int $mail_port = 465;

    /**
     * Nombre de la marca, marca personal o empresa.
     *
     * @var string
     */
    private string $mail_company_name = "Tu marca";

    /**
     * Correo de contacto.
     *
     * @var string
     */
    private string $mail_contact = "contact@tu-dominio.com";

    /**
     * Credenciales de las variables de entorno.
     *
     * @var object|null
     */
    private ?object $credentials = null;

    /**
     * Debe pasar las credenciales como argumento
     */
    private function __construct(object $credentials) {
        $this->credentials = $credentials;
        $this->load_credentiales();
    }

    /**
     * Carga las credenciales de acceso a la base de datos a partir de las variables de entorno. Si
     * las variables a las que se intentan acceder no existen, entonces, dejará los valores por
     * defectos intactos.
     *
     * @return void
     */
    private function load_credentiales(): void {

        $this->production = $this->get_value('DL_PRODUCTION', $this->production);
        $this->host = $this->get_value('DL_DATABASE_HOST', $this->host);
        $this->port = $this->get_value('DL_DATABASE_PORT', $this->port);
        $this->username = $this->get_value('DL_DATABASE_USER', $this->username);
        $this->password = $this->get_value('DL_DATABASE_PASSWORD', $this->password);
        $this->database = $this->get_value('DL_DATABASE_NAME', $this->database);
        $this->charset = $this->get_value('DL_DATABASE_CHARSET', $this->charset);
        $this->collation = $this->get_value('DL_DATABASE_COLLATION', $this->collation);
        $this->drive = $this->get_value('DL_DATABASE_DRIVE', $this->drive);
        $this->prefix = $this->get_value('DL_PREFIX', $this->prefix);
        $this->mail_host = $this->get_value('MAIL_HOST', $this->mail_host);
        $this->mail_username = $this->get_value('MAIL_USERNAME', $this->mail_username);
        $this->mail_password = $this->get_value('MAIL_PASSWORD', $this->mail_password);
        $this->mail_port = $this->get_value('MAIL_PORT', $this->mail_port);
        $this->mail_company_name = $this->get_value('MAIL_COMPANY_NAME', $this->mail_company_name);
        $this->mail_contact = $this->get_value('MAIL_CONTACT', $this->mail_contact);
    }

    /**
     * Devuelve el valor de la variable de entorno o el valor por defecto de la propiedad
     *
     * @param string $property
     * @return mixed
     */
    private function get_value(string $property, mixed $default_value = null): mixed {

        if (!isset($this->credentials->{$property})) {
            return $default_value;
        }

        if (!isset($this->credentials->{$property}['value'])) {
            return $default_value;
        }

        /**
         * Valor capturado de las credenciales.
         * 
         * @var string
         */
        $value = $this->credentials->{$property}['value'];

        /**
         * Tipo actual del valor captura de la variable de entorno.
         * 
         * @var string
         */
        $actual_type = gettype($value);

        /**
         * Tipo esperado para la propiedad seleccionada.
         * 
         * @var string
         */
        $expected_type = gettype($default_value);

        $this->validate_type($property, $actual_type, $expected_type);

        return $value;
    }

    /**
     * Valida el tipo de datos correspondiente a la propiedad.
     *
     * @param string $value Valor a ser analizado.
     * @param string $expected Tipo esperado.
     * @param string $varname Variable afectada.
     * @return void
     */
    private function validate_type(string $varname, string $actual, string $expected): void {

        if ($actual !== $expected) {
            $this->error($varname, $actual, $expected);
        }
    }

    /**
     * Valida si el tipo esperado es válido.
     *
     * @param string $actual_type Tipo actual.
     * @param string $expected_type Tipo de datos esperado.
     * @param string $varname Variable afectada
     * @return void
     */
    private function error(string $varname, string $actual_type, string $expected_type): void {
        header("Content-Type: application/json; charset=utf-8", true, 500);

        echo DLOutput::get_json([
            "status" => false,
            "error" => "Error de tipo de datos",
            "details" => [
                "actual" => $actual_type,
                "expected" => $expected_type,
                "varname" => $varname
            ]
        ], true);

        exit;
    }

    public function is_production(): bool {
        return $this->production;
    }

    public function get_host(): string {
        return $this->host;
    }

    public function get_port(): int {
        return $this->port;
    }

    public function get_username(): string {
        return $this->username;
    }

    public function get_password(): string {
        return $this->password;
    }

    public function get_database(): string {
        return $this->database ?? '';
    }

    public function get_charset(): string {
        return $this->charset;
    }

    public function get_collation(): string {
        return $this->collation;
    }

    public function get_drive(): string {
        return strtolower(trim($this->drive));
    }

    public function get_prefix(): string {
        return $this->prefix;
    }

    public function get_mail_host(): string {
        return $this->mail_host;
    }

    public function get_mail_username(): string {
        return $this->mail_username;
    }

    public function get_mail_password(): string {
        return $this->mail_password;
    }

    public function get_mail_port(): int {
        return $this->mail_port;
    }

    public function get_mail_company_name(): string {
        return $this->mail_company_name;
    }

    public function get_mail_contact(): string {
        return $this->mail_contact;
    }

    public static function get_instance(object $credentials): self {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self($credentials);
        }

        return self::$instance;
    }
}
