<?php

namespace DLTools\HttpRequest;

use DLRoute\Requests\DLRequest;
use DLTools\Config\DLValues;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use DLTools\Compilers\DLMarkdown;
use DLTools\Config\Credentials;
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

    use DLValues;
    use DLConfig;

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
        $request = DLRequest::get_instance();
        static::$values = $request->get_values();
    }

    /**
     * Permite enviar un correo electrónico. Los campos del formulario permitidos
     * son los siguientes:
     *
     * `email`, `cc`, `bcc`, `name`, `lastname`, `subject`, `body` y `altbody`.
     * 
     * `replyto` se integra en las variables de entorno.
     * 
     * @param string $email Requerido. Correo electrónico destinatario
     * @param string $body Requerido. Cuerpo del mensdaje.
     * @param ?string $altbody Opcional. Proporciona información alternativa del cuerpo.
     * @param string $subject Opcional. Asunto del mensaje.
     * @param string | null $name Opcional. Nombre del remitente.
     * @param string | null $lastname Opcional. Apellidos del remitente.
     * @param string | null $cc Opcional. Copia
     * @param string | null $bcc Opcional. Copia oculta.
     * 
     * @return array
     */
    public function send(
        string $email,
        string $body,
        ?string $altbody = null,
        string $subject = "",
        ?string $name = null,
        ?string $lastname = null,
        ?string $cc = null,
        ?string $bcc = null
    ): array {

        /**
         * Credenciales que provienen de las variables
         * de entorno.
         * 
         * @var Credentials $credentials
         */
        $credentials = $this->get_credentials();

        if (is_null($email) || !($this->is_email($email))) {
            $this->error_type("Formato de correo inválido hacia el destinatario");
        }

        /**
         * Dirección de correo electrónico a la que se deben enviar las respuestas
         * al mensaje.
         * 
         * @var string $replyto
         */
        $replyto = $credentials->get_mail_contact();

        /**
         * Nombre y apellido del remitente.
         * 
         * @var string $name
         */
        $full_name = $this->sanitizeString(
            ($name ?? '') . " " . ($lastname ?? '')
        );

        /**
         * Asunto del mensaje.
         * 
         * @var string $subject
         */
        $subject = trim($subject);

        /**
         * Cuerpo del mensaje.
         * 
         * @var string $body
         */
        $body = trim($body);
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
        $altbody = $this->sanitizeString($this->get_input('altbody') ?? '');

        /**
         * Correo remitente.
         * 
         * @var string $username
         */
        $username = $credentials->get_mail_username();

        /**
         * Contraseña del remitente
         * 
         * @var string $password
         */
        $password = $credentials->get_mail_password();

        /**
         * Puerto del servidor de correo.
         * 
         * @var int $port
         */
        $port = $credentials->get_mail_port();

        /**
         * Servidor SMTP del servidor de correos electrónicos.
         * 
         * @var string $emailhost
         */
        $emailhost = $credentials->get_mail_host();

        /**
         * Nombre del remitente.
         * 
         * @var string $companyName
         */
        $companyName = $credentials->get_mail_company_name();

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
            $mailer->addAddress($email, $full_name);
            $mailer->addReplyTo($replyto, $companyName);

            if (is_string($cc)) {
                $cc = trim($cc);

                if (!($this->is_email($cc))) {
                    $this->error_type("Formato de correo inválido en \$cc");
                }

                $mailer->addCC($cc, $full_name);
            }

            if (is_string($bcc)) {
                $bcc = trim($bcc);

                if (!($this->is_email($bcc))) {
                    $this->error_type("Formato de correo inválido en \$bcc");
                }

                $mailer->addBCC($bcc, $full_name);
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
            $this->exception($error, true);
        }

        return [];
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
