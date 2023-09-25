# Uso de la clase **DLUser**

Lo primero es instanciar la clase `DLUser` :

``` php
$user = new DLUser();
```

El primer paso a seguir es verificar si ya existía un `token` :

``` php
$token = $user -> obtenerToken();
```

Comprobamos si el usuario ya se encontraba autenticado, de lo contraio se debe autenticar:

``` php
...
$hash = $st -> fetch()["hash"];

// Antes de autenticar se debe verificar primero
// lo esté:
if ( ! $user -> autenticado( $hash ) ) {
  $user -> user = $_POST['user'];
  $user -> password = $_POST['password'];

  // Si el hash se creró se enviará a la base 
  // de datos en función del usuario y contraseña:
  if ( $user -> crearHash() ) {
    $actualizar -> execute([
      ":hash" => $user -> hash,
      ":usuario" => $user -> user,
      ":clave" => sha1($user -> password)
    ]);

    $user -> autenticar( $user -> hash );
  }
  
}
```

Donde `$hash` es el valor obtenido de la base de datos y `$user -> hash` es el generado por el servidor si no se encuentra en la base de datos.

**Debe tomarse en cuenta que:**

* El método `$user -> crearHash()` devolverá `true` si los datos enviados por el usuario a `$user -> user` y `$user -> password` son válidos, de lo contrario, devolverá `false` . Si devuelve `true` creará un `hash` en la propiedad `$user -> hash` . 

* El método `$user -> autenticar( $user -> hash )` permite crear las cookies con los hashes generados por el servidor que serán comparados con los que se almacenó en la base de datos.

Para comprobar que el usuario se ha autenticado solo tiene que escribir:

``` php
if ( $user -> autenticado( $hash ) ) {
  # Instruciones que se ejecutan con el usuario autenticado
}
```

Para destruir los datos de la sesión del usuario se debe utilizar:

``` php
$user -> salir();
```

Si resultó existoso `$user -> salir();` devolverá un `true` , de lo contrario, devolverá un `false` .

> **IMPORTANTE:**
>  
> Es posible que la biblioteca no se encuentra completa por el momento porque se encuentra en su fase inicial de desarrollo. Pero, será mejoradas en cada actualización.
