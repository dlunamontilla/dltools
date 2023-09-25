# Explicación de la clase `DLUploads`

## Método `getAssocArrayFromFiles`

La función `getAssocArrayFromFiles` toma un argumento opcional de tipo `string` que representa la clave del archivo. La función tiene como objetivo crear un array asociativo a partir de los archivos subidos al servidor mediante un formulario enviado mediante el método `POST`.

La función primero comprueba si la clave proporcionada existe en la variable global `$_FILES`, si no es así, la función regresa un array vacío. Luego se asigna el valor de la variable `$_FILES[$key]` a una variable llamada `$files`.

Se comprueba si la variable `$name` es un array. Si es así, se obtiene la longitud del _array_, si no es así, se asigna `1`.

Luego se realiza un bucle **for** para recorrer los elementos del _array_ `$name` y se crea un _array_ vacío llamado `$file`. Se asignan los valores a esta variable desde el array `$files`.

Finalmente se verifica si el primer elemento del array `$aux` esta vacío, si es así se retorna un _array_ vacío, en caso contrario se retorna el array `$aux`.

```php
<?php
use DLTools\Controllers\DLDatabase;
$db = new DLDatabase;
$data = $db->from('users')->get();
```
