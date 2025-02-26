## [v0.2.2]

### Eliminación del alias `is_null` en DLDatabase

- Se eliminó el alias `is_null` que apuntaba al método `field_is_null` para evitar colisiones de nombres, particularmente con la clase `DLConfig`.

## [v0.2.1]

### Mejoras en `Model`
1. **Corrección en `set_params`**
   - Se corrigió la asignación de la tabla en el método estático `set_params` para mejorar la coherencia con la configuración del modelo.

2. **Ajuste en `get()`**
   - Se igualó el comportamiento del método `get()` con el de `DLDatabase`, eliminando el llamado innecesario a `select`.

3. **Ajuste en `first()`**
   - Se igualó el comportamiento del método `first()` con el de `DLDatabase`, eliminando el llamado innecesario a `select`.

4. **Nuevo alias `is_null`**
   - Se creó un alias `is_null` de `field_is_null` para facilitar su uso.

### Mejoras en `DLDatabase`
1. **Corrección en la gestión de la instancia única**
   - Se agregó `self::$instance->clean();` dentro del método estático `get_instance()` para llamar al método protegido `clean()`, asegurando que cada nueva instancia se inicialice correctamente.

### Novedades

1. **Soporte para `SELECT DISTINCT`**
   - Se agregó compatibilidad con la cláusula `DISTINCT`, permitiendo consultas más eficientes cuando se requieren valores únicos.

2. **Almacenamiento de datos en formato binario**
   - Ahora es posible almacenar datos en formato binario dentro del sistema de base de datos, ampliando las capacidades de manejo de información.

3. **Almacenamiento de credenciales de emergencia**
   - Se agregó una opción para almacenar credenciales de emergencia cuando no es posible definir `env.type` como variable de entorno con tipos estáticos.

---

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

