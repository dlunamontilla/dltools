# Uso de la clase DLProtocol

La clase `DLProtocol` forza al sitio Web a utilizar `HTTPS` mediante el método `DLProtocol::https()`.

Para utilizarla, debes registrar los dominios en donde serán obligatorios su uso.

**Por ejemplo:**

``` php
$protocol = new DLProtocol([
  "dominio.com",
  "dominio2.com",
  "dominio3.com",
  ...
  "dominioN.com"
]);
```

Y llamar el método `https()` :

``` php
$protocolo->https();
```