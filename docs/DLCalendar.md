# Uso de la clase DLCalendario():

> **Importante:** Se debe actualizar esta documentación


**Sintaxis:**

``` php
DLCalendario::crear( int $mes, int $año ) : array
```

Para utilizar la clase `DLCalendario()` debe escribir lo siguiente:

``` php
$calendario = new DLCalendario();
```

Y luego llamar el método `crear()` :

``` php
$calendario->crear( 8, 2020 );
```

Donde `8` es el mes de agosto y `2020` el año utilizado en la función.
