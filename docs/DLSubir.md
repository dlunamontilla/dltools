# Funcionamiento de la clase `DLSubir()`

Antes de utilizar la clase `DLSubir` debes instalar primero `php-imagick`.

Desde Ubuntu u otras distribuciones basadas en Debian:

``` bash
sudo apt install php-imagick
```

Si en su distribución no está disponible o tiene un sistema operativo diferente puede visitar [PHP: Instalación - Manual](https://www.php.net/manual/es/imagick.installation.php)

Para utilizarlo se debe definir la siguiente sintaxis:

``` php
<?php
// Se instancia la clase DLSubir:
$subir = new DLSubir([
  "ruta" => "./path/uploads",
  "tipo" => "imagen"
]);

// Y se llama el método archivo del objeto $subir: 
$array = $subir->archivo( "fichero" );
?>
```

Se creará `$array` con los siguientes datos almacenados en él:

``` none
Array
(
  [0] => Array
    (
      [fichero] => ./path/uploads/2020/07/images.jpeg
      [thumbnail] => ./path/uploads/2020/07/thumbnail/103ddce489912777e93ae386aa3f1efaaadfcf2f.jpeg
      [info] => No se encontraron errores
      [error] => 
    )

)
```

**Donde:**

+ **`[fichero]`:** es el que contiene la ruta donde se guardó la imagen.
+ **`[thumbnail]`:** es el que contiene la ruta de la miniatura creada a partir de la imagen original.
+ **`[info]`:** Informa al usuario del éxito del proceso de envío de archivos.
+ **`[error]`:** Contiene un valor de tipo `booleano`, indicando con `true` si se produjo un error, o de lo contrario, con `false` si todo fue existoso.
