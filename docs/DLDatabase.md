# Documentación básica de la clase DLDatabase

## Constructor de consultas

Si desea construir la siguiente consulta:

```sql
SELECT * FROM tabla
```

Solo debes escribir la siguiente línea:

```php
$db = new DLDatabase;
$db->from('tabla');
```

Sin embargo, solo construye la consulta, pero no obtiene los datos de una base de datos. Para obtenerla, solo hay que escribirla completa:

```php
$data = $db->from('tabla')->get();
```

Para la obtención de todo todo el registro de una tabla.

Pero puede obtener solo el primer registro de la tabla a partir de la siguiente línea:

```php
$data = $db->from('tabla')->first();
```

### Obtener algunos campos de una tabla

---

Para obtener algunos campos de la tabla, con múltiples registros, escriba la siguiente línea:

```php
$data = $db->select('campo1', 'campo2')->from('tabla')->get();
```

Para obtener datos de algunos campos con el primer registro, escriba la siguiente línea:

```php
$data = $db->select('campo1', 'campo2')->from('tabla')->first();
```

### Creación de registros en una tabla

Para insertar nuevos registros en una tabla SQL debe escribir las siguientes líneas:

```php
$db->to('products')->insert([
    'name' => 'David',
    'lastname' => 'Luna'
]);
```
