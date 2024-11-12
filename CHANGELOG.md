# Changelog

Todas las modificaciones importantes de este proyecto se documentarán en este archivo.

El formato es el siguiente:

- `Added` para nuevas funcionalidades.
- `Changed` para cambios en funcionalidades existentes.
- `Deprecated` para funcionalidades que pronto serán eliminadas.
- `Removed` para funcionalidades eliminadas de esta versión.
- `Fixed` para corrección de errores.
- `Security` para correcciones de vulnerabilidades.

## [v0.1.56] - 2024-11-12

### Deprecated

- `DLTools\HttpRequest\DLHost`: Toda la clase `DLHost` fue marcada como obsoleta en la versión v0.1.55, y se recomienda usar `DLRoute\Server\DLHost` en su lugar. Esta clase será eliminada en versiones futuras.
  - **Métodos deprecados en `DLHost`**:
    - `getHostname()`: Utilizar `DLRoute\Server\DLHost::get_hostname()` en su lugar.
    - `getDomain()`: Utilizar `DLRoute\Server\DLHost::get_domain()` en su lugar.
    - `isHTTPS()`: Utilizar `DLRoute\Server\DLHost::is_https()` en su lugar.
    - `https()`: Este método fue marcado como obsoleto desde v0.1.55 y se recomienda manejar redirecciones HTTPS en `DLRoute\Server\DLHost`.

- `DLTools\DLTools::isHTTPS`: Este método fue marcado como obsoleto y se recomienda usar `DLRoute\Server\DLHost::is_https()` en su lugar. Esta funcionalidad será eliminada en versiones futuras.

### Removed

- Ninguna funcionalidad fue eliminada en esta versión, aunque se prevé la eliminación de las clases y métodos deprecados en una próxima actualización para optimizar el código y reducir redundancias.

---

## [v0.1.55] - 2024-10-01

### En proceso de deprecación

- Se ha comenzado el proceso de deprecación de métodos y clases que duplican funcionalidad con `DLRoute\Server\DLHost`.
- `DLHost` fue declarada obsoleta con la intención de centralizar la gestión de host y HTTPS en `DLRoute\Server\DLHost`.
