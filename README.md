# Herramienta DLTools para DLUnire

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
- Lectura de variables de entorno con tipado estático, a la vez, que permite la lectura de las variables de entorno si archivos de variables de entorno.

Para establecer variables de entorno para autenticar su aplicación con el motor de base de datos, cree un archivo con el nombre `.env.type` y coloque las siguientes líneas:

```envtype
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
# entonces, el valor por defecto serà `utf8`:
DL_DATABASE_CHARSET: string = "utf8"

# Colación del motor de base de datos. Si no se define, el valor por
# defecto será `utf8_general_ci`:
DL_DATABASE_COLLATION: string = "utf8_general_ci"

# Motor de base de datos. Si no se define esta variable, el valor
# por defecto será `mysql`:
DL_DATABASE_DRIVE: string = "mysql"
```

Si además, necesita enviar correos electrónicos, pegue las siguientes líneas:

```envtype
# Usuario de correo electrónico. Tome en cuenta que no debe colocar
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

Si desea instalar las _API Key_ de Google para instalar un `reCAPTCHA` puede agregar las siguientes líneas en el archivo `.env.type`:

```entype
# Estas variables son opcionales. Si desea establecer un reCAPTCHA
# de Google, puedes definirlas aquí:
G_SECRET_KEY: string = "<tu-llave-privada>"
G_SITE_KEY: string = "<tu-llave-del-sitio>"
```

## Uso de la herramienta

### Modelos

Si desea crear una clase extendendida en un modelo, debe escribir las siguientes líneas:

```php

<?php

namespace TuApp\Models;

use DLTools\Database\Model;

class Products extends Model {}
```

Donde `Products` es la clase que hace referencia a la tabla `products`. Si las tablas de la aplicación usa prefijos, por ejemplo, `wp_`, entonces, deberá definir el prefijo en el archivo `.env.type`:

```envtype
DL_PREFIX: string = "wp_"
```

O si la variable de entorno que ha definido no usa ningún tipo de archivo, asegúrese definirla en su proveedor de hosting (por ejemplo, Heroku), debe tener este nombre de variable:

```none
DL_PREFIX = "wp_"
```

Si en el modelo `Products` desea establecer un nombre de tabla diferente, solo tiene que definirla así:

```php
class Products extends Model {

  protected static ?string $table = "otra_tabla";
}
```

Además, ya cuenta con métodos disponibles para interactuar con la base de datos, por ejemplo y que puedes utilizar desde un controlador:

```php

  final class TestController extends Controller {

    /**
     * Ejemplo de interacción con el modelo `Products`.
     * 
     * @return array
     */
    public function products(): array {
      new Products();

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
       * Número de registro por página.
       * 
       * @var integer
       */
      $paginate = Products::paginate($page, $rows);

      return [
        "count" => $count,
        "register" => $register,
        "paginate" => $paginate
      ]
    }
  }
```

El fragmento anterior es un ejemplo básico de lo que hace el modelo `Products`, pero no solamente tiene métodos para recuperar registros, también para almacenar nuevos registros, por ejemplo:

```php
final class TestController extends Controller {

  public function products(): array {
    $products = new Products();

    $products->product_name = $products->required('product_name');
    $products->product_description = $product->required('product_description');
    $products->save();


    return [];
  }
}
```

### Envío de correos electrónicos

Esta herramienta utiliza `PHPMailer` para enviar correos electrónicos.

Para enviar correos electrónicos desde su controlador, puede hacerlo de la siguiente forma:

```php
final class TestController extends Controller {

  /**
   * Ejemplo de envío de correos electrónicos.
   * 
   * @return array
   */
  public function mail(): array {

    $email = new SendMail();

    return $email->send(
      $email->get_email('email_field'),
      $body->get_required('body_field') # Puede contener código HTML
    );
  }
}
```

Además, debe agregar previamente las siguientes líneas en el archivo `.env.type` para poder enviar correos electrónicos:

```envtype
# Servidor SMTP:
MAIL_HOST: string = "smtp.su-hosting.com"

# Cuenta de correo que enviará su correo electrónico:
MAIL_USERNAME: email = no-reply@su-dominio.com

# Contraseña de su cuenta de correo `no-reply@su-dominio.com`:
MAIL_PASSWORD: string = "contraseña"

# Correo electrónico en el que recibirá respuesta
MAIL_CONTACT: email = contact@su-dominio.com

```

### Sistema de autenticación

Para implementar un sistema de autenticación básico, debería crear una clase extendida en `DLUser` como se observa en las siguientes líneas:

```php
use DLTools\Auth\DLAuth;
use DLTools\Auth\DLUser;

class Users extends DLUser {

  public function capture_credentials(): void {
    /**
     * Autenticación del usuario
     * 
     * @var DLAuth
     */
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

Esta documentación se irá actualizando progresivamente sobre el uso completo de esta herramienta. Apenas esto es una parte.
