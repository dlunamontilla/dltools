# Changelog

Todas las modificaciones importantes de este proyecto se documentarán en este archivo.

El formato es el siguiente:

- `Added` para nuevas funcionalidades.
- `Changed` para cambios en funcionalidades existentes.
- `Deprecated` para funcionalidades que pronto serán eliminadas.
- `Removed` para funcionalidades eliminadas de esta versión.
- `Fixed` para corrección de errores.
- `Security` para correcciones de vulnerabilidades.

## [v0.1.58] - 2024-12-11

### Added

- Ninguna funcionalidad nueva introducida en esta versión.

### Changed

- **DLRoute**:

  - **DLUpload Trait**:
    - Los siguientes métodos cambiaron su visibilidad de `protected` a `public` para permitir un acceso más flexible:
      - `upload_file(string $field, string $type = "*/*"): array`: Maneja la carga de archivos en el servidor.
      - `get_filenames(): array`: Devuelve los nombres de los archivos cargados.
      - `set_basedir(string $basedir): void`: Establece el directorio base para los archivos cargados.
      - `set_thumbnail_width(int $width): void`: Configura la anchura de los thumbnails.
      - `get_absolute_path(string $relative_path): string`: Devuelve la ruta absoluta de un archivo dado su ruta relativa.

- **DLTools**:
  - Se realizó un `composer update` para mantener las dependencias actualizadas. No se detectaron cambios funcionales significativos tras la actualización.

### Deprecated

- Ninguna funcionalidad marcada como obsoleta en esta versión.

### Removed

- Ninguna funcionalidad eliminada en esta versión.

### Fixed

- Ningún error corregido en esta versión.

### Security

- Ninguna corrección de vulnerabilidades en esta versión.
