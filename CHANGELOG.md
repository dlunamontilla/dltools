# Changelog

Todas las modificaciones importantes de este proyecto se documentarán en este archivo.

El formato es el siguiente:

- `Added` para nuevas funcionalidades.
- `Changed` para cambios en funcionalidades existentes.
- `Deprecated` para funcionalidades que pronto serán eliminadas.
- `Removed` para funcionalidades eliminadas de esta versión.
- `Fixed` para corrección de errores.
- `Security` para correcciones de vulnerabilidades.

## [v0.1.63] - 2024-12-14

### Changed

- **DLDatabase / Query Builder**:
  - Se actualizó el método `where_in()` para aceptar un array de valores en lugar de utilizar el spread operator, permitiendo además el uso de un tercer parámetro opcional `$logical` (por defecto `DLDatabase::AND`) para definir el operador lógico al combinar condiciones.
  
### Removed

- **DLDatabase / Query Builder**:
  - Se removió la versión anterior del método `where_in()` que utilizaba la firma `where_in(string $field, string ...$values)`.

### Added

- Ninguna funcionalidad nueva introducida en esta versión.

### Deprecated

- Ninguna funcionalidad marcada como obsoleta en esta versión.

### Fixed

- Ningún error corregido en esta versión.

### Security

- Ninguna corrección de vulnerabilidades en esta versión.

---

## [v0.1.62] - 2024-12-13

### Changed

- **DLDatabase / Query Builder**:
  - Se corrigió la estructura condicional de la consulta, limpiando las consultas condicionales para evitar incoherencias.
  - Se mantiene el soporte para `IS NULL` e `IS NOT NULL` y la corrección de conflictos entre consultas relacionados con la precedencia de operadores lógicos.

### Added

- Ninguna funcionalidad nueva introducida en esta versión.

### Deprecated

- Ninguna funcionalidad marcada como obsoleta en esta versión.

### Removed

- Ninguna funcionalidad eliminada en esta versión.

### Fixed

- Ningún error corregido en esta versión.

### Security

- Ninguna corrección de vulnerabilidades en esta versión.

---

## [v0.1.59] - 2024-12-12

### Changed

- **DLDatabase / Query Builder**:
  - Se modificó el método `where` para permitir el uso de `IS NULL` e `IS NOT NULL`, además de corregir conflictos entre consultas relacionados con la precedencia de operadores lógicos.

### Added

- Ninguna funcionalidad nueva introducida en esta versión.

### Deprecated

- Ninguna funcionalidad marcada como obsoleta en esta versión.

### Removed

- Ninguna funcionalidad eliminada en esta versión.

### Fixed

- Ningún error corregido en esta versión.

### Security

- Ninguna corrección de vulnerabilidades en esta versión.

---

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