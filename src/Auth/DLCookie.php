<?php

namespace DLTools\Auth;

use DLRoute\Server\DLServer;
use DLTools\HttpRequest\DLHost;
use Exception;

/**
 * Permite establecer los parámetros de las cookies y las cookies de sesión.
 * 
 *  ### Ejemplo de uso
 * 
 * ```
 * <?php
 * use DLTools\Auth\DLCookie;
 * 
 * $cookie = new DLCookie();
 * $cookie->set_name('mi_cookie');
 * $cookie->set_value('valor_de_cookie');
 * $cookie->set_path('/');
 * $cookie->set_domain('dominio.com');
 * $cookie->set_secure(true);
 * $cookie->create_cookie(3600);
 * ```
 * 
 * @package namespace DLTools\Auth;
 * @version 0.0.1 (release);
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2024 David E Luna M
 * @license MIT
 * 
 */
final class DLCookie {

    /**
     * Las cookies se envían en solicitudes del mismo sitio y en solicitudes GET de tercer partido.
     * Esto significa pero no en otros tipos de solicitudes, como POST o cuando se cargan elementos
     * de un sitio de terceros en la página. Este es un valor más equilibrado, ya que proporciona cierta
     * protección contra ataques CSRF mientras mantiene la funcionalidad en la navegación normal.
     * 
     * @var string LAX
     */
    public const LAX = 'Lax';

    /**
     * Las cookies solo se envían en solicitudes del mismo sitio. Esto significa que si un
     * usuario navega a un sitio web desde otro sitio (por ejemplo, haciendo clic en un enlace),
     * las cookies no se enviarán. Este valor ofrece el mayor nivel de seguridad, pero puede
     * afectar la funcionalidad del sitio, especialmente si se requieren cookies para la autenticación
     * o el seguimiento entre sitios.
     * 
     * @var string STRICT
     */
    public const STRICT = 'Strict';

    /**
     * Las cookies se envían en todas las solicitudes, incluidas las solicitudes de tercer partido.
     * Sin embargo, para usar este valor, debes asegurarte de que la cookie tenga el atributo Secure establecido,
     * lo que significa que la cookie solo se enviará a través de conexiones HTTPS. Este valor es útil para
     * aplicaciones que requieren el intercambio de cookies en contextos cross-site, pero también puede presentar
     * riesgos de seguridad.
     * 
     * @var string NONE
     */
    public const NONE = 'None';

    /**
     * Tiempo de vida de la cookie hasta el cierre del navegador.
     * 
     * @var int LIFETIME_SESSION
     */
    public const LIFETIME_SESSION = 0;

    /**
     * Tiempo de vida de una hora.
     * 
     * @var int LIFETIME_HOUR
     */
    public const LIFETIME_HOUR = 3600;

    /**
     * Tiempo de vida de medio día.
     * 
     * @var int LIFETIME_HALF_DAY
     */
    public const LIFETIME_HALF_DAY = 43200;

    /**
     * Tiempo de vida de un día completo.
     * 
     * @var int LIFETIME_DAY
     */
    public const LIFETIME_DAY = 86400;

    /**
     * Tiempo de vida de una semana.
     * 
     * @var int LIFETIME_WEEK
     */
    public const LIFETIME_WEEK = 604800;

    /**
     * Tiempo de vida de un mes (30 días aprox).
     * 
     * @var int LIFETIME_MONTH
     */
    public const LIFETIME_MONTH = 2592000;

    /**
     * Tiempo de vida de seis meses.
     * 
     * @var int LIFETIME_HALF_YEAR
     */
    public const LIFETIME_HALF_YEAR = 15552000;

    /**
     * Tiempo de vida de un año.
     * 
     * @var int LIFETIME_YEAR
     */
    public const LIFETIME_YEAR = 31536000;

    /**
     * Tiempo de vida de dos años, útil para configuraciones persistentes.
     * 
     * @var int LIFETIME_TWO_YEARS
     */
    public const LIFETIME_TWO_YEARS = 63072000;

    /**
     * Tiempo de vida muy largo, para cookies que se quieran almacenar indefinidamente.
     * 
     * @var int LIFETIME_INFINITE
     */
    public const LIFETIME_INFINITE = 2147483647;

    /**
     * Nombre de la cookie
     *
     * @var string $name
     */
    private string $name;

    /**
     * Valor de la cookie
     *
     * @var string $value
     */
    private string $value;

    /**
     * Ruta de la cookie. El valor por defecto es `/'.
     *
     * @var string
     */
    private string $path = '/';

    /**
     * Dominio de la cookie. El valor por defecto se establece durante la instancia de clase.
     *
     * @var string $domain
     */
    private string $domain;

    /**
     * Permite indicar si la cookie se envía a través de conexiones HTTPS
     *
     * @var boolean $secure
     */
    private bool $secure;

    /**
     * Indica si JavaScript puede tener acceso a la cookie
     *
     * @var boolean $http_only
     */
    private bool $http_only = true;

    /**
     * Establece la forma en la que se enviará una cookie.
     *
     * @var string $samesite
     */
    private string $samesite = 'Lax';

    /**
     * Establece el tiempo de vida de la cookie.
     * El tiempo de vida por defecto oscila alrededor de un mes.
     *
     * @var integer
     */
    private int $lifetime;

    /**
     * Al momento de instanciarse la clase se establece un tiempo de vida y dominio por defecto, que
     * es donde se ejecuta la aplicación, sin embargo, puede cambiar la configuración utilizando los métodos
     * `set_lifetime(DLCookie::LIFETIME_MONTH)` y `set_domain('tudominio.com')`.
     * 
     * Puede utilizar el timepo de vida que desee, pero estos son ejemplos referenciales.
     * 
     * @return void
     */
    public function __construct() {
        $this->domain = DLServer::get_hostname();
        $this->lifetime = self::LIFETIME_MONTH;
        $this->secure = DLHost::isHTTPS();
    }

    /**
     * Establece el nombre de la Cookie
     *
     * @param string $name Nombre de la cookie
     * @return void
     * 
     * @throws Exception
     */
    public function set_name(string $name): void {

        if (empty(trim($name))) {
            throw new Exception('El nombre de la cookie es requerido', 500);
        }

        $this->name = trim($name);
    }

    /**
     * Establece el valor que tendrá la cookie
     *
     * @param string $value
     * @return void
     */
    public function set_value(string $value): void {
        $this->value = trim($value);
    }

    /**
     * Establece la ruta de la cookie
     *
     * @param string $path
     * @return void
     */
    public function set_path(string $path): void {
        $this->value = trim($path);
    }

    /**
     * Establece un dominio personalizado
     *
     * @param string $domain Dominio personalizado
     * @return void
     */
    public function set_domain(string $domain): void {
        $this->domain = trim(strtolower($domain));
    }

    /**
     * Establece si la cookie se enviará solo a través de conexiones HTTPS.
     *
     * @param boolean $secure El valor por defecto es `true`, es decir, solo conexiones seguras.
     * @return void
     */
    public function set_secure(bool $secure = true): void {
        $this->secure = $secure;
    }

    /**
     * Envía una cookie a un cliente HTTP restringiendo el acceso desde JavaScript.
     *
     * @param boolean $http_only El valor por defecto es `true`, es decir, con acceso restringido a JavaScript.
     * @return void
     */
    public function set_http_only(bool $http_only = true): void {
        $this->http_only = $http_only;
    }

    /**
     * Determina cómo se envían las cookies al cliente HTTP.
     *
     * @param string $samesite El valor por defecto es `DLCookie::LAX`
     * @return void
     */
    public function set_samesite(string $samesite = self::LAX): void {
        $this->samesite = trim($samesite);
    }

    /**
     * Establece el tiempo de vida de la cookie.
     *
     * @param integer $time Tiempo de vida en segundos
     * @return void
     * 
     * @throws Exception
     */
    public function set_lifetime($time = self::LIFETIME_MONTH): void {

        if ($time >= self::LIFETIME_INFINITE) {
            throw new Exception('No se permite tiempos de vida muy largos por razones de seguridad', 500);
        }

        if ($time > 0) {
            $this->lifetime = $time;
        }
    }

    /**
     * Crea una variabe de sesión con los parámetros establecidos.
     * 
     * > Importante: este método debe ser llamado antes de llamar a `session_start()`
     *
     * @return void
     */
    public function create_session(): void {
        session_set_cookie_params([
            'lifetime' => time() + $this->lifetime,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->http_only,
            'samesite' => $this->samesite,
        ]);
    }

    /**
     * Crea una cookie con los parámetros personalizados
     *
     * @return void
     */
    public function create_cookie(): void {
        setcookie($this->name, $this->value, [
            'expires' => time() + $this->lifetime,
            'path' => $this->path,
            'domain' => $this->domain,
            'secure' => $this->secure,
            'httponly' => $this->http_only,
            'samesite' => $this->samesite,
        ]);
    }
}
