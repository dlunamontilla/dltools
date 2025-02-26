## [v0.2.0]

### Novedades de la versión

- **Compatibilidad con nuevos motores de base de datos**  
  Se añade soporte para:
  - **PostgreSQL (`psql`)**: Puerto predeterminado `5432`. Para su uso, se debe instalar la extensión `php-pgsql`.
  - **SQLite (`sqlite`)**: No requiere configuración de puertos. 
  - **MariaDB/MySQL**: Funcionalidad compatible.  
  *Nota:* Se prevé la incorporación de nuevos motores en futuras versiones.

> **Importante:** Para utilizar estos motores en PHP, es necesario instalar las siguientes extensiones:
>
> Para **SQLite**:
> ```bash
> sudo apt install php-sqlite3
> ```
>
> Para **PostgreSQL**:
> ```bash
> sudo apt install php-pgsql
> ```

- **Subconsultas en la propiedad estática `$table`**  
  Ahora se permiten subconsultas en la definición de la tabla en los modelos. Ejemplos:
  ```php
  // Subconsulta simple:
  protected static ?string $table = "SELECT * FROM tabla";
  
  // Consulta más compleja con parámetros:
  protected static ?string $table = "SELECT * FROM tabla WHERE record_status = :record_status";
  
  // Uso de un nombre de tabla personalizado:
  protected static ?string $table = "otra_tabla";
  ```

- **Nuevo método `replace` en el modelo**  
  Se incorpora el método `replace` (compatible, por el momento, con MariaDB/MySQL), que actualiza el registro si ya existe, funcionando de manera similar a `create` o `insert`:
  ```php
  Tabla::replace([
      "campo" => "valor"
  ]);
  ```

- **Método `show_tables()` para listar tablas**  
  Se agrega un método estático que muestra las tablas de la base de datos con soporte para paginación. Ejemplos:
  ```php
  // Especificando página y número de registros:
  DLDatabase::show_tables(1, 50);
  
  // Uso con parámetros predeterminados:
  DLDatabase::show_tables();
  ```

- **Incorporación del método `between` en las consultas**  
  Se añade el método `between`, que permite realizar consultas filtrando valores dentro de un rango determinado. Ejemplo:
  ```php
  Employee::between('age', new ValueRange(18, 25))->get();
  ```

- **Soporte del método `between` en el modelo `Model`**  
  Ahora los modelos pueden utilizar `between` directamente en sus consultas, lo que mejora la flexibilidad en los filtros de búsqueda.

- **Nueva clase `ValueRange`**  
  Se introduce la clase `ValueRange`, que permite definir intervalos de valores de manera clara y estructurada:
  ```php
  $range = new ValueRange(10, 50);
  ```
  Su uso en consultas se realiza de la siguiente manera:
  ```php
  Employee::between('salary', $range)->get();
  ```

## [v0.1.66]

- Se agregó el método `set_params`.
- Se optimizaron diversas características del sistema.

---

## [v0.1.65] - 2025-02-17

### Cambios

- **Modelo**:
  - Se actualizó el método `having()` para emplear la nueva implementación en `DLDatabase`, permitiendo el uso correcto de la cláusula `HAVING` en las consultas SQL.
  - Se revisó la documentación del método `having()`, reemplazando referencias previas a `where` por `having`.

---

## [v0.1.64] - 2025-02-16

### Añadido

- **DLDatabase / Query Builder**:
  - Incorporación del método `having()` para agregar condiciones a la cláusula `HAVING` en consultas SQL que involucren agrupaciones (`GROUP BY`).

### Cambios

- **DLDatabase / Query Builder**:
  - Actualización de la documentación del método `having()`, indicando que, por el momento, utiliza la propiedad `$this->where` para concatenar las condiciones.

---

## [v0.1.63] - 2025-02-09

### Cambios

- **DLDatabase / Query Builder**:
  - Se modificó el método `where_in()` para aceptar un array de valores, eliminando el uso del spread operator.  
  - Se añadió un tercer parámetro opcional `$logical` (por defecto `DLDatabase::AND`) para definir el operador lógico al combinar condiciones.

### Eliminado

- Se retiró la versión anterior del método `where_in()` que utilizaba la firma:
  ```php
  where_in(string $field, string ...$values)
  ```

---

## [v0.1.62] - 2024-12-13

### Cambios

- **DLDatabase / Query Builder**:
  - Se corrigió la estructura condicional de las consultas, asegurando la coherencia en las condiciones.
  - Se mantuvo el soporte para `IS NULL` e `IS NOT NULL`, resolviendo conflictos relacionados con la precedencia de operadores lógicos.

---

## [v0.1.59] - 2024-12-12

### Cambios

- **DLDatabase / Query Builder**:
  - Se actualizó el método `where` para permitir el uso de `IS NULL` e `IS NOT NULL`, solucionando problemas de precedencia entre operadores lógicos.

---

## [v0.1.58] - 2024-12-11

### Cambios

- **DLRoute / DLUpload Trait**:
  - Se modificó la visibilidad de los siguientes métodos de `protected` a `public` para facilitar un acceso más flexible:
    - `upload_file(string $field, string $type = "*/*"): array`
    - `get_filenames(): array`
    - `set_basedir(string $basedir): void`
    - `set_thumbnail_width(int $width): void`
    - `get_absolute_path(string $relative_path): string`

- **DLTools**:
  - Se realizó un `composer update` para mantener las dependencias al día, sin cambios funcionales significativos.
