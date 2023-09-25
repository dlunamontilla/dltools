<?php

namespace DLTools\HttpRequest;

use DLTools\Auth\DLAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use DLTools\Compilers\DLMarkdown;
use DLTools\Config\DLConfig;

/**
 * Permite enviar correos electrónicos utilizando la biblioteca 
 * PHPMailer.
 * 
 * @package DLTools
 * 
 * @author David E. Luna M. <davidlunamontilla@gmail.com>
 * @license MIT
 * @version 1.0.0
 */
class SendMail {
    /**
     * Instancia de PHPMailer
     *
     * @var PHPMailer
     */
    private PHPMailer $mail;

    /**
     * Procesa la petición enviada al servidor.
     *
     * @var DLRequest
     */
    private DLRequest $request;

    /**
     * Objecto de autenticación. Allí se encuentra el token.
     *
     * @var DLAuth
     */
    private DLAuth $auth;

    /**
     * Configuración de conexión del proyecto
     *
     * @var DLConfig
     */
    private DLConfig $config;

    /**
     * Nivel de depuración del servidor
     * 
     * @var int $level
     */
    private int $level = 0;

    /**
     * Ayuda a determinar si la cadena que leerá es Markdown o
     * directamente, código HTML. 
     * 
     * El valor por defecto es `false`. Si se establece a `true`
     * entonces, la cadena se parseará como sintaxis `Markdown`.
     * 
     * Cuando sea `true` no se recomienda colocar código HTML, porque
     * los eliminará.
     *
     * @var boolean
     */
    private bool $markdown = false;

    public function __construct() {
        $this->config = DLConfig::getInstance();

        $this->mail = new PHPMailer(true);
        $this->request = DLRequest::getInstance();
        $this->auth = new DLAuth;
    }

    /**
     * Permite enviar un correo electrónico. Los campos del formulario permitidos
     * son los siguientes:
     *
     * `csrf-token`, `email`, `replyto`, `cc`, `bcc`, `name`, `lastname`, `subject`, `body` y `altbody`.
     * 
     * Donde:
     * 
     * - `csrf-token`: Un token de seguridad que se utiliza para proteger un formulario de correo electrónico de ataques de cross-site request forgery (CSRF).
     * - `email`: La dirección de correo electrónico del destinatario principal del mensaje.
     * - `replyto`: La dirección de correo electrónico a la que se deben enviar las respuestas al mensaje.
     * - `cc` (copia carbón): Las direcciones de correo electrónico de los destinatarios a los que se les enviará una copia visible del mensaje.
     * - `bcc` (copia de carbón oculta): Las direcciones de correo electrónico de los destinatarios a los que se les enviará una copia invisible del mensaje.
     * - `name`: El nombre del remitente.
     * - `lastname`: El apellido del remitente.
     * - `subject`: El asunto del mensaje.
     * - `body`: El cuerpo del mensaje.
     * - `altbody`: Una versión alternativa del cuerpo del mensaje que se puede utilizar si el cliente de correo electrónico no admite el formato original.
     * 
     * Siendo los campos `csrf-token` e `email` obligatorios.
     * 
     * El primer argumento que pases son los campos del formulario 
     * que hayas establecidos en su código HTML, por ejemplo:
     * 
     * ```
     * $mail->send([
     *  'csrf-token' => true,
     *  'email' => true,
     *  'g-response-recaptcha' => true // Eso es en caso de usar el recaptcha de Google (recomendado).
     * ]);
     * ```
     * 
     * Donde `true` indica que el campo es requerido (obligatorio), es decir, no debe estar vacío.
     * 
     * @param array $param Parámetros de la petición a validar.
     * 
     * @param array $optionFields Esto permite reemplazar el valor de los campos del formulario
     * que se hayan enviado, o en su defecto, simular que son campos del formulario. Si los campos que ha agregado
     * no son los que están arriba mencionado, entonces, se ignorarán.
     * 
     * @return array
     */
    public function send(array $param, array $optionFields = []): array {
        $mail = $this->mail;

        $is_valid = $this->request->post($param);

        if (!$is_valid) return [
            "send" => false,
            "message" => "Los parámetros de la petición no son válidos"
        ];

        $values = $this->request->getValues();

        if (!isset($values['csrf-token'])) {
            throw new \Error("El campo \"csrf-token\" es obligatorio");
        }

        $token = $values['csrf-token'] ?? 'Token';

        if ($token !== $this->auth->getToken()) {
            return [
                "send" => false,
                "message" => "Sospecha de ataque CSRF con petición ilegítima"
            ];
        }

        foreach ($optionFields as $key => $value) {
            $values[$key] = $value;
        }

        $values = (object) $values;

        if (!isset($values->email)) {
            throw new \Error("El campo `email` es obligatorio");
        }

        /**
         * Credenciales que provienen de las variables
         * de entorno.
         * 
         * @var object $credentials
         */
        $credentials = $this->config->getCredentials();

        /**
         * Correo destinatario.
         * 
         * @var string $email
         */
        $email = $this->sanitizeEmail($values->email ?? '');

        /**
         * Dirección de correo electrónico a la que se deben enviar las respuestas
         * al mensaje.
         * 
         * @var string $replyTo
         */
        $replyTo = $this->sanitizeEmail($values->replyto ?? ($credentials->MAIL_REPLY ?? ''));

        /**
         * Las direcciones de correo electrónico de los destinatarios a los que se
         * les enviará una copia visible del mensaje.
         * 
         * @var string $cc
         */
        $cc = $this->sanitizeEmail($values->cc ?? '');

        /**
         * Las direcciones de correo electrónico de los destinatarios a los que se
         * les enviará una copia invisible del mensaje.
         * 
         * @var string $bcc
         */
        $bcc = $this->sanitizeEmail($values->bcc ?? '');

        /**
         * Nombre y apellido del remitente.
         * 
         * @var string $name
         */
        $name = $this->sanitizeString(($values->name ?? '') . ($values->lastname ?? ''));

        /**
         * Asunto del mensaje.
         * 
         * @var string $subject
         */
        $subject = $this->sanitizeString($values->subject ?? '');

        /**
         * Cuerpo del mensaje.
         * 
         * @var string $body
         */
        $body = trim($values->body ?? '');

        $body = $this->decodeString($body);

        if ($this->markdown) {
            $body = DLMarkdown::stringMarkdown($body);
        }

        /**
         * Una versión alternativa del cuerpo del mensaje que se puede utilizar si
         * el cliente de correo electrónico no admite el formato original.
         * 
         * @var string $altbody
         */
        $altbody = $this->sanitizeString($values->altbody ?? '');

        if (!$this->checkEmail($email)) {
            return [
                "send" => false,
                "message" => 'El correo electrónico ingresado no es válido'
            ];
        }

        /**
         * Correo remitente.
         * 
         * @var string $username
         */
        $username = $this->sanitizeEmail($credentials->MAIL_USERNAME ?? 'contact@' . $this->sanitizeString(DLHost::getHostname()));

        /**
         * Contraseña del remitente
         * 
         * @var string $password
         */
        $password = $credentials->MAIL_PASSWORD ?? '';

        /**
         * Puerto del servidor de correo.
         * 
         * @var int $port
         */
        $port = (int) $credentials->MAIL_PORT ?? 465;

        /**
         * Host del servidor de correo electrónico.
         * 
         * @var string $emailhost
         */
        $emailhost = $this->sanitizeString($credentials->MAIL_HOST ?? '');

        /**
         * Nombre del remitente.
         * 
         * @var string $companyName
         */
        $companyName = $this->sanitizeString($credentials->MAIL_COMPANY_NAME ?? '');

        # Uso de la biblioteca `PHPMailer`
        $mailer = new PHPMailer(true);

        try {

            # Configuración del servidor:
            $mailer->SMTPDebug = $this->level;
            $mailer->isSMTP();
            $mailer->Host = $emailhost;
            $mailer->SMTPAuth = true;
            $mailer->Username = $username; # Usuario SMTP
            $mailer->Password = $password; # Contraseña SMTP
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mailer->Port = $port;

            # De:
            $mailer->setFrom($username, $companyName);

            # Destinatario:
            $mailer->addAddress($email, $name);

            if (!empty(trim($replyTo))) {
                $mailer->addReplyTo($replyTo, $companyName);
            }

            if (!empty(trim($cc))) {
                $mailer->addCC(trim($cc));
            }

            if (!empty(trim($bcc))) {
                $mailer->addBCC(trim($bcc));
            }

            # Datos adjuntos | Inhabilitado en esta versión
            // foreach($files as $key => $file) {
            //     $mailer->addAttachment($file->name);
            // }

            # Contenido de correo electrónico:
            $mailer->isHTML(true);
            $mailer->Subject = trim($subject);
            $mailer->Body = trim($body);
            $mailer->AltBody = trim($altbody);

            $mailer->send();

            return [
                "send" => true,
                "message" => 'Envío exitoso de correo electrónico'
            ];
        } catch (Exception $error) {
            return [
                "send" => false,
                "message" => 'No se pudo enviar el mensaje. Se produjo una excepción durante el envío. Error: ' . $mailer->ErrorInfo
            ];
        }
    }

    /**
     * Verificar si la cadena de texto es un correo electrónico.
     *
     * @param string $email
     * @return boolean
     */
    public function checkEmail(string $email): bool {
        $isEmail = (FALSE !== filter_var($email, FILTER_VALIDATE_EMAIL));

        if ($isEmail) {
            list($user, $domain) = explode('@', $email);
            return checkdnsrr($domain, 'MX');
        }

        return $isEmail;
    }

    /**
     * Elimina caracteres que no forman parte de un nombre válido de correo electrónico.
     *
     * @param string $email
     * @return string
     */
    public function sanitizeEmail(string $email): string {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return (string) $email;
    }

    /**
     * Sanea una cadena de caracteres.
     *
     * @param string $text
     * @return string
     */
    public function sanitizeString(string $text): string {
        $text = filter_var($text, FILTER_SANITIZE_ENCODED | FILTER_SANITIZE_SPECIAL_CHARS);
        return (string) $text;
    }

    /**
     * Verifica si la está vacía la cadena de texto que se pasa como argumento.
     *
     * @param string $text
     * @return string
     */
    public function isEmpty(string $text): bool {
        return empty(trim($text));
    }

    /**
     * Estos son los niveles de depueración posibles:
     * 
     * - `0` (cero): Desactiva la depuración SMTP y no muestra ninguna información de depuración.
     * 
     * - `1`: Muestra información básica sobre la conexión y la entrega del correo electrónico.
     * 
     * - `2`: Muestra información detallada sobre la conexión, la autenticación y la entrega
     * del correo electrónico.
     * 
     * - `3`: Muestra información detallada y mensajes de protocolo brutos para la conexión, la
     * autenticación y la entrega del correo electrónico.
     * 
     * Es importante seleccionar el nivel de depuración adecuado dependiendo del tipo de información
     * que se requiera. En general, se recomienda utilizar un nivel de depuración más bajo (1 o 2) mientras
     * se está resolviendo un problema, y luego desactivar la depuración SMTP (establecer en 0) una vez que
     * se haya resuelto el problema. Utilizar un nivel de depuración más alto (3) puede ser útil en casos en
     * los que se requiere ver información más detallada y mensajes de protocolo brutos para solucionar
     * un problema específico.
     * 
     * Si establece un valor diferentes a los antes mencionados tomará `0` (cero) por defecto.
     * 
     * Ejemplo de uso:
     * 
     * ```
     * $mail->setDebug(2);
     * ```
     * 
     * Debe colocar la línea anterior antes de colocar la siguiente línea:
     * 
     * ```
     * $mail->send($fields, $options);
     * ```
     *
     * @param integer $level Valor del argumento por defecto es `0` (cero) para `setDebug`.
     * @return void
     */
    public function setDebug(int $level = 0): void {
        $this->level = $level;
    }

    /**
     * Solicita que se parsee contenido en formato `Markdown`.
     * 
     * Si se pasa como argumento el valor `false` desactivará el
     * parseo de contenidos `Markdown`.
     * 
     * Ten en cuenta que si el modo `Ḿarkdown` se encuentra activado
     * cualquier contenido HTML será eliminado.
     *
     * @param boolean $markdown
     * @return void
     */
    public function setMarkdown(bool $markdown = true): void {
        $this->markdown = $markdown;
    }

    /**
     * Decodifica una cadena de texto de un formato a otro.
     *
     * @param string $text
     * @return string
     */
    private function decodeString(string $text): string {
        $encoded_text = mb_convert_encoding($text, "ISO-8859-1");
        return $encoded_text;
    }
}
