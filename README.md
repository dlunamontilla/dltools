# Herramienta DLTools para DLUnire

## Versión de la biblioteca

La versión actual de la biblioteca es `v0.2.0`.


## Instalación

Para instalar `dlunamontilla/dltools` debe escribir el siguiente comando:

```zsh
composer require dlunamontilla/dltools
```

> **Importante:** debe tener instalado previamente _composer_ para poder instalar esta herramienta. Si desea instalar _composer_ [visite su sitio Web oficial](https://getcomposer.org "Administrador de dependencia de PHP") y siga las instrucciones.

## Características

Esta herramienta cuenta con lo siguiente:

- Constructor de consultas.
- Modelo.
- Lectura de variables de entorno con tipado estático, a la vez que permite la lectura de las variables de entorno sin archivos de variables de entorno.
  El analizador sintáctico de las variables de entorno lee el nombre de la variable, el tipo definido y si el valor coincide con el tipo que se indicó en la variable de entorno. Es capaz de verificar la validez de una cadena `UUIDv4` y correo electrónico.

  En esta herramienta, el correo electrónico se considera un tipo de datos.

- *Parser* para archivos que terminen en `*.template.html`. El motor de plantillas tiene sintaxis muy similar a **Laravel**.

## Similitud con Laravel

Mientras que en el motor de plantillas de Laravel se utiliza la directiva `@extends('base')` para tomar la plantilla base, en esta herramienta se utiliza la directiva `@base('base')`.

1. Tanto en `Laravel` como en `DLUnire` (ya que `DLTools` se hizo para usar con el framework `DLUnire`) las plantillas se encuentran en el directorioi `/resources/`.

2. En `Laravel` las plantillas terminan en `.blade.php`, mientras que en DLUnire o DLTools terminan en `.template.html`.

3. Puede imprimir en pantalla, en formato JSON, al igual que en `Laravel` un array o un objeto así:
   ```scss
    <pre>@json($array, 'pretty')</pre>
   ```

   Se utilizó como segundo argumento `'pretty'` para indicar que debe imprimirse formateado.

4. También puede incluir archivos `markdown` utilizando la directiva `@markdown('vista')`, sin extensiones, ya que la extensión `.md` se lo agrega directamente la directiva.

    Puede establecer la ruta de la misma forma que se hace con la función `view()`.

    Ejemplo de código para incorporar archivos **markdown** como parte del código HTML ya parseado:

    ```scss
    <div class="container">
      @markdown('archivo-markdown')
    </div>
    ```

    > Tome e cuenta que los archivos Markdown se guardan también en el directorio `/resources/`, es decir, en la misma ruta que las plantilas `.template.html`.
    >
    > Cuando cree su archivo Markdown a incluir en la plantilla no olvide colocarle la extensión `.md` al momento de crear el archivo, pero no agrege la extensión en la directiva `@markdown`, ya que ella se encarga de ello.
    >
    > Para la directiva `@markdown` los puntos (`.`) son separadores de rutas.

5. **Ciclos:**

    Para definir ciclos o bucles en `.template.html` se hace de la misma forma que en **Laravel**, es decir:

    ```scss
    @for ($index = 0; $index < 10; ++$index)
      <div>Tu código HTML ({$index + 1})</div>
    @endfor
    ```



> ## Importante
>
> La documentación todavía no está completa y se está trabajando para terminarla.

Para establecer variables de entorno para autenticar su aplicación con el motor de base de datos, cree un archivo con el nombre `.env.type` y coloque las siguientes líneas:

```bash
# Indica si la aplicación debe correr o no en producción:
DL_PRODUCTION: boolean = false

# Servidor de la base de datos:
DL_DATABASE_HOST: string = "localhost"

# Puerto del motor de la base de datos:
DL_DATABASE_PORT: integer = 3306

# Usuario de la base de datos:
DL_DATABASE_USER: string = "tu-usuario"

# Contraseña de la base de datos:
DL_DATABASE_PASSWORD: string = "tu-contraseña"

# Nombre de la base de datos:
DL_DATABASE_NAME: string = "tu-base-de-datos"

# Codificación de caracteres de la base de datos. Si no se define, 
# entonces, el valor por defecto será `utf8`:
DL_DATABASE_CHARSET: string = "utf8"

# Colación del motor de base de datos. Si no se define, el valor por
# defecto será `utf8_general_ci`:
DL_DATABASE_COLLATION: string = "utf8_general_ci"

# Motor de base de datos. Si no se define esta variable, el valor
# por defecto será `mysql`:
DL_DATABASE_DRIVE: string = "mysql"
```

Si además necesita enviar correos electrónicos, pegue las siguientes líneas:

```bash
# Usuario de correo electrónico. No debe colocar
# comillas de ningún tipo, porque no se evaluaría si es un correo:
MAIL_USERNAME: email = no-reply@example.com

# Contraseña del correo electrónico:
MAIL_PASSWORD: string = "Contraseña de correo"

# Puerto del servidor SMTP con certificado SSL para tu correo 
# electrónico:
MAIL_PORT: integer = 465

# Nombre de la empresa que envía el correo a través de su web
# o aplicación:
MAIL_COMPANY_NAME: string = "Empresa, marca o tu marca personal"

# Correo de contacto:
MAIL_CONTACT: email = contact@example.com
```

> **Importante:** para el resaltado de sintaxis, instale **[DL Typed Environment](https://marketplace.visualstudio.com/items?itemName=dlunamontilla.envtype "DL Typed Environment")**

Si desea instalar las _API Key_ de Google para implementar un `reCAPTCHA`, puede agregar las siguientes líneas en el archivo `.env.type`:

```bash
# Estas variables son opcionales. Si desea establecer un reCAPTCHA
# de Google, puede definirlas aquí:
G_SECRET_KEY: string = "<tu-llave-privada>"
G_SITE_KEY: string = "<tu-llave-del-sitio>"
```

## Uso de la herramienta

### Modelos

Si desea crear una clase extendida en un modelo, debe escribir las siguientes líneas:

```php
<?php

namespace TuApp\Models;

use DLTools\Database\Model;

class Products extends Model {}
```

Donde `Products` es la clase que hace referencia a la tabla `products` o `dl_products` si en la variable de entorno se define el prefijo `dl_` de esta forma: DL_PREFIX: string = "dl_".
Si las tablas de la aplicación usan prefijos, por ejemplo, `wp_`, entonces deberá definir el prefijo en el archivo `.env.type`:

```bash
DL_PREFIX: string = "dl_"
```

Si en el modelo `Products` desea establecer un nombre de tabla diferente, solo tiene que definirla así:

```php
<?php
class Products extends Model {
  protected static ?string $table = "otra_tabla";
}
```

En el modelo puede agregar una subconsulta así:

```php
<?php
class Products extends Model {
  protected static ?string $table = "SELECT * FROM tabla WHERE record_status = :record_status";
}
```

Y DLTools detectará de que se trata de una subconsulta automáticamente.

#### Interacción con la base de datos desde un controlador

```php
<?php
use DLTools\Core\BaseController;

final class TestController extends BaseController {

  /**
   * Ejemplo de interacción con el modelo `Products`.
   * 
   * @return array
   */
  public function products(): array {
    /**
     * Devuelve, máximo 100 registros
     * 
     * @var array $register
     */
    $register = Products::get();

    /**
     * Devuelve un número total de registros almacenados en la tabla
     * `products`.
     * 
     * @var integer $count
     */
    $count = Products::count();

    /**
     * Número de páginas
     * 
     * @var integer
     */
    $page = 1;

    /**
     * Número de registros por página.
     * 
     * @var integer
     */
    $paginate = Products::paginate($page, 50);

    return [
      "count" => $count,
      "register" => $register,
      "paginate" => $paginate
    ];
  }
}
```

### Creación de registros

```php
<?php

use DLTools\Core\BaseController;

final class TestController extends BaseController {

  public function products(): array {
    $created = Products::create([
      "product_name" => $this->get_required('product-name'),
      "product_description" => $this->get_input('product-description')
    ]);

    http_response_code(201);
    return [
      "status" => $created,
      "success" => "Producto creado exitosamente."
    ];
  }
}
```

### Envío de correos electrónicos

Esta herramienta utiliza `PHPMailer` para enviar correos electrónicos.

```php
<?php
use DLTools\Core\BaseController;

final class TestController extends BaseController {

  /**
   * Ejemplo de envío de correos electrónicos.
   * 
   * @return array
   */
  public function mail(): array {
    $email = new SendMail();

    return $email->send(
      $this->get_email('email_field'),
      $this->get_required('body_field') // Puede contener código HTML
    );
  }
}
```

### Sistema de autenticación

```php
<?php
use DLTools\Auth\DLAuth;
use DLTools\Auth\DLUser;

class Users extends DLUser {
  public function capture_credentials(): void {
    $auth = DLAuth::get_instance();

    $this->set_username(
      $this->get_required('username')
    );

    $this->set_password(
      $this->get_required('password')
    );
    
    $auth->auth($this, [
      "username_field" => 'username',
      "password_field" => 'password',
      "token_field" => 'token'
    ]);
  }
}
```

---

## Documentación

Esta documentación se irá actualizando progresivamente sobre el uso completo de esta herramienta.

La herramienta `DLTools` tiene funcionalidades muy extensas para ser documentada en tiempos muy breves.
