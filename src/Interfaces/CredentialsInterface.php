<?php

namespace DLTools\Interfaces;

/**
 * Defuelve las credenciales de las variables de entorno si estas
 * están definidas, de lo contrario, devolverá valores predeterminados.
 * 
 * @package DLTools\Interfaces
 * 
 * @version 1.0.0 (release)
 * @author David E Luna <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 * 
 * @method bool is_production()
 * @method string get_host()
 * @method integer get_port()
 * @method string get_username()
 * @method string get_password()
 * @method string get_database()
 * @method string get_charset()
 * @method string get_collation()
 * @method string get_drive()
 * @method string get_prefix()
 * @method string get_mail_host()
 * @method string get_mail_username()
 * @method string get_mail_password()
 * @method string get_mail_port()
 * @method string get_mail_company_name()
 * @method string get_mail_contact()
 */
interface CredentialsInterface {

    /**
     * Indica si el entorno de ejecución de la aplicación debe ser producción o desarrollo.
     * 
     * El valor por defecto devuelto es `false` para indicar que se está trabajando en
     * un ambiente de desarrollo si la variable `DL_PRODUCTION` no está definida.
     * 
     * Si la variable ha sido definida, el valor devuelto será la definida en la variable.
     * 
     * En su archivo de varaibles entorno `.env.type` debe indicar si el proyecto corre
     * en producción o desarrollo así:
     * 
     * ```envtype
     * DL_PRODUCTION: boolean = true
     * ``` 
     *
     * @return boolean
     */
    public function is_production(): bool;

    /**
     * Devuelve el nombre del host del motor de la base de datos de la aplicación. El valor devuelto por defecto si la variable `DL_DATABASE_HOST` no está definida es `localhost`
     * 
     * Para indicar el servidor de ejecución de la base de datos, debe agregar esta línea en
     * el archivo `.env.type`:
     * 
     * ```envtype
     * DL_DATABASE_HOST: string = "server"
     * ```
     *
     * @return string
     */
    public function get_host(): string;

    /**
     * Devuelve el número de puerto del motor de base de datos. El valor por defecto es
     * `3306` si la variable `DL_DATABASE_PORT` no se encuentra definida.
     * 
     * La variable `DL_DATABASE_PORT` debe ser definido como entero como se observa a continuación:
     * 
     * ```envtype
     * DL_DATABASE_PORT: integer = 3306
     * ```
     *
     * @return integer
     */
    public function get_port(): int;

    /**
     * Devuelve el nombre de usuario de la base de datos. El nombre de usuario devuelto por
     * defecto es `root` si la variable `DL_DATABASE_USER` no se encuentra definida.
     * 
     * Para establecer un nombre de usuarios, debe definir la variable `DL_DATABASE_USER` de la
     * siguiente manera:
     * 
     * ```envtype
     * DL_DATABASE_USER: string = "tu-usuario"
     * ```
     *
     * @return string
     */
    public function get_username(): string;

    /**
     * Devuelve la contraseña de la base de datos. La contraseña, por defecto, es un `string` vacío si la variable `DL_DATABASE_PASSWORD` no se encuentra definida.
     * 
     * Si desea establecer una contraseña, debe escribir lo siguiente en el archivo `.env.type`:
     * 
     * ```envtype
     * DL_DATABASE_PASSWORD: string = "tu-contraseña"
     * ```
     *
     * @return string
     */
    public function get_password(): string;

    /**
     * Devuelve el nombre de la base de datos. El nombre por defecto de la base de datos es un `string` vacío si la variable `DL_DATABASE_NAME` no se encuentra definida.
     * 
     * Si desea establecer un nombre de base de datos, debe escribir la siguiente línea en el 
     * archivo `.env.type`:
     * 
     * ```envtype
     * DL_DATABASE_NAME: string = "database_name"
     * ```
     *
     * @return string
     */
    public function get_database(): string;

    /**
     * Devuelve la codificación de caracteres definida en la variable de entorno `DL_DATABASE_CHARSET`. Si la variable no está definida o el archivo no existe, devolverá `utf8`.
     * 
     * Para establecer una codificación de caracteres, debe escribir en el archivo `.env.type` lo siguiente:
     * 
     * ```envtype
     * DL_DATABASE_CHARSET: string = "utf8"
     * ```
     *
     * @return string
     */
    public function get_charset(): string;

    /**
     * Devuelve `colletion` definida en la variable de entorno `DL_DATABASE_COLLATION`
     * que se utiliza para determinar cómo se ordenan y comparan los caracteres en una
     * base de datos.
     * 
     * Si la variable `DL_DATABASE_COLLATION` no está definida o no existe el archivo
     * el valor devuelto por defecto es `utf8_general_ci`.
     * 
     * Para definir una `colación` debe escribir la siguiente línea en `.env.type`:
     * 
     * ```envtype
     * DL_DATABASE_COLLATION: string = "utf8_general_ci"
     * ```
     *
     * @return string
     */
    public function get_collation(): string;

    /**
     * Devuelve el motor de base de datos establecido en la variable de entorno
     * `DL_DATABASE_DRIVE`. Si la variable de entorno no está definida o no existe
     * el archivo `.env.type`, entonces, el valor devuelto por defecto es `mysql`.
     * 
     * Si desea definir un motor de base de datos diferente, debe escribir en el archivo
     * `.env.type` la siguiente línea:
     * 
     * ```envtype
     * DL_DATABASE_DRIVE: string = "mysql"
     * ```
     *
     * @return string
     */
    public function get_drive(): string;

    /**
     * Devuelve el prefijo que se usarán en las tablas de la base de datos dinifidas
     * en `DL_PREFIX`. Si `DL_PREFIX` no está definida, entonces, el valor devuelto
     * por defecto será una cadena de texto vacía, es decir, sin prefijos.
     * 
     * Si desea establecer un prefijo, debe escribir en el archivo `.env.type` la
     * siguiente línea:
     * 
     * ```envtype
     * DL_PREFIX: string = "prefijo_"
     * ```
     * Se RECOMIENDA, pero no es obligatorio definir un prefijo que termine en `(_)`. Los
     * prefijos NO DEBEN definirse con guiones bajos `(_)` al comienzo ni tener cualquier
     * otro tipo de carácter especial.
     * 
     * @return string
     */
    public function get_prefix(): string;

    /**
     * Devuelve el host o servidor de correo electrónicos definida en la variable de
     * entorno. Si `MAIL_HOST` no está definida, devolverá un host SMTP de ejemplo,
     * por ejemplo, este: `smtp.example.com`.
     * 
     * Para establecer un host SMTP válido, debe escribir en `.env.type` la
     * siguiente línea:
     * 
     * ```envtype
     * MAIL_HOST: string = "smtp.tu-hosting.com"
     * ```
     *
     * @return string
     */
    public function get_mail_host(): string;

    /**
     * Devuelve el usuario del correo electrónico establecido en la variable de
     * entorno `MAIL_USERNAME`. Si la variable no está definida, entonces devolverá
     * un correo de ejemplo, por ejemplo, `no-reply@tu-dominio.com`.
     * 
     * Para definir un usuario de correo electrónico que se usará para enviar
     * correos electrónicos, entonces, debe escribir en el archivo `.env.type`
     * la siguiente línea:
     * 
     * ```envtype
     *  MAIL_USERNAME: email = no-reply@tu-dominio.com
     * ```
     * 
     * > Recuerda que el correo electrónico se define sin comillas en la variable de entorno con el tipo `email` para que conseguir que se valide de forma automática.
     *
     * @return string
     */
    public function get_mail_username(): string;

    /**
     * Devuelve la contraseña del correo electrónico que se utilizará para enviar
     * correos a través de la aplicación definida en la variable `MAIL_PASSWORD`.
     * 
     * Si la variable `MAIL_PASSWORD` no se encuentra definida devolverá una cadena
     * vacía.
     * 
     * Para establecer una contraseña para tu correo electrónico, deberá escribir
     * en el archivo `.env.type` la siguiente línea:
     * 
     * ```envtype
     * MAIL_PASSWORD: string = "Tu-contraseña"
     * ```
     *
     * @return string
     */
    public function get_mail_password(): string;

    /**
     * Devuelve el número de puerto SMTP del servidor de correo electrónico definido en la variable de entorno `MAIL_PORT`.
     * 
     * Si la variable o el archivo no existe, entonces, devolverá `465` como valor por defecto.
     * 
     * Si desea establecer un número de puerto SMTP diferente, deberá escribir en el
     * archivo `.env.type` la siguiente línea:
     * 
     * ```envtype
     * MAIL_PORT: integer = 465
     * ```
     * 
     * Donde `465` es el número de puerto que has definido, pero puede ser un valor
     * diferente, por ejemplo:
     * 
     * ```envtype
     * MAIL_PORT: integer = 900
     * ```
     * 
     * @return integer
     */
    public function get_mail_port(): int;

    /**
     * Devuelve el nombre de la empresa, marca o marca persona definida en la variable
     * `MAIL_COMPANY_NAME`. Si la variable o archivo `.env.type` no existe, entonces,
     * devolverá como valor por defecto `Tu marca`.
     * 
     * Si desea establecer un nombre de marca, empresa o marca personal debe escribir
     * en `.env.type` la siguiente línea:
     * 
     * ```envtype
     * MAIL_COMPANY_NAME: string = "El nombre de tu marca"
     * ```
     *
     * @return string
     */
    public function get_mail_company_name(): string;

    /**
     * Devuelve el correo electrónico de contacto definido en la variable de entorno. Si
     * la variable `MAIL_CONTACT` o el archivo `.env.type` no existe, entonces, devolverá
     * un correo electrónico por defecto, por ejemplo, `contact@tu-dominio.com`
     * 
     * Para establecer un correo electrónico de contacto, deberá escribir en el archivo
     * `.env.type` la siguiente línea:
     * 
     * ```envtype
     * MAIL_CONTACT: email = contact@tu-correo.com
     * ```
     *
     * @return string
     */
    public function get_mail_contact(): string;
}