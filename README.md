# DLTools

> **IMPORTANTE:** Todavía no se han documentado todas las clases de DLTools, porque no se han implementado todas las funciones hasta el momento. Se está desarrollando y se estará actualizando las documentaciones en tiempo real.
>
> Se está preparando la biblioteca para que pueda ser utilizable. Incluye actualización de la documentación.

---

Para instalar esta herramienta escriba la siguiente línea en un terminal:

```bash
composer require dlunamontilla/dltools
```

Y luego, en su archivo index.php, escriba la siguiente línea:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

O si está en `public_html/`, simplemente escriba dentro del archivo `index.php` la siguiente línea:

```php
<?php
require_once dirname(__DIR__, 1) . '/vendor/autoload.php';
```

Una vez hecho lo anterior, puede usar cualquiera de las clases de DLTools en su aplicación.

Por ejemplo, si va a utilizar la clase `Database` solo tiene que escribir la siguiente línea:

```php
use DLTools\Models\Database;
$db = new Database;

$data = $db
    ->query('SELECT * FROM users WHERE id = :id')
    ->get([':id' => 1]);

print_r($data);
```

## Credenciales de la base de datos

Para poder conectarse a una base de datos se necesita crear un archivo `.env` en la raíz de su proyecto.

Y luego, copiar y pegar las siguientes líneas en él:

```env
DL_DATABASE_HOST = localhost
DL_DATABASE_PORT = 3306
DL_DATABASE_USER = usuario
DL_DATABASE_PASSWORD = contraseña
DL_DATABASE_NAME = database
DL_DATABASE_CHARSET = utf8
DL_DATABASE_COLLATION = utf8_general_ci
DL_DATABASE_PREFIX = dl_
DL_DATABASE_DRIVE = mysql

DL_API_URL = http://localhost/api/
DL_API_KEY = 123456789
```

Se recomienda que el directorio donde cargue su sitio web sea `public_html/` o `public/`, y desde allí cargue el archivo `.env` que se encuentra en la raíz de su proyecto que es la que contiene el directorio antes mencionado.

Es decir:

```bash
raiz/
  ├── public_html
    │  └── index.php
    │  └── assets/
  ├── .env
  ├── vendor/
    └── autoload.php
```
