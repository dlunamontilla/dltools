# DLRequest

La clase `DLRequest` procesa una gran variedad de peticiones del usuario.

Con ella se pueden obtener valores de los formularios, así como validar parámetros de la petición.

1. **Constructor:** en ella se determina el método utilizado en la petición.

   ```php
   DLRequest::__construct();
   ```

2. **`getValue()`:** este método permite obtener el valor de una petición a partir de una clave (`$key`). Si el parámetro de la petición no existe devolverá un string vacío.

    **Descripción:**

    ```php
    DLRequest::getValue(string $key): string
    ```

    **Uso:**

    ```php
    $request = new DLRequest;
    $value = $request->getValue('name');
    ```

3. **`getValues()`:** Devuelve todos los valores de los parámetros de la petición en un array dentro de una petición válida, independientemente de su método de envío.

   **Descripción:**

   ```php
   DLResquest::getValues(string $prefix = NULL, array $fields = []): array
   ```

   **`$prefix`:** el valor por defecto es `NULL`. Cualquier valor puesto es el prefijo de las claves del _array_ de valores que devolverá.

   **`$fields`:** El valor por defecto es un _array_ vacío. Si queremos seleccionar algunos campos o parámetros de la petición, solo tenemos que especificarlo en el _array_ que pasaremos como segundo argumento.

   Por ejemplo:

   ```php
   ['param1', 'param2',...'paramN']
   ```

   **Uso:**

   Utilizando el método sin argumentos:

   ```php
   $request = new DLRequest;
   $values = $request->getValues();
   ```

   El resultado almacenado en `$values` es el que sigue:

   ```php
   Array(
      "param1" => "Valor del parámetro",
      "param2" => "Otro valor del parámetro",
      ...
      "paramN" => "Valor del último parámetro"
   )
   ```

   Donde `$values` es el _array_ con valores de una petición válida.

   **Utilizándolo con prefijo:**

   ```php
   $request = new DLRequest;
   $values = $request->getValues(':');
   ```

   El valor almacenado en `$values` será:

    ```php
   Array(
      ':param1' => "Valor del parámetro",
      ':param2' => "Otro valor del parámetro",
      ...
      ':paramN' => "Valor del último parámetro"
   )
   ```

   Donde los dos puntos (`:`) es el prefijo. Útil para sentencias SQL preparadas.

   **Utilizándolo sin prefijo, pero algunos parámetros:**

   ```php
   $request = new DLRequest;
   $values = $request->getValues(NULL, [
      "param1", "param3"
   ]);
   ```

   El valor almacenado en `$values` será:

   ```php
   Array(
      "param1" => "Valor del primer parámetro",
      "param3" => "Valor del tercer parámetro"
   )
   ```

   **Utilizándolo con prefijo y seleccionando algunos campos o parámetros:**

   ```php
   $request = new DLRequest;
   $values = $request->getValues(':', [
      "param1", "param4"
   ]);
   ```

   El valor almacenado en `$values` será:

   ```php
   Array(
      ":param1" => "Valor del primer parámetro con prefijo",
      ":param4" => "Valor del 4to parámetro con prefijo"
   )
   ```

   **`post()`:** permite validar que los parámetros de una petición hecha mediante el método `POST` sean válidos.

   **Descripción:**

   ```php
   DLRequest::post(array $parameters): bool
   ```

   **Uso:**

   ```php
   $request = new DLRequest;

   if ($request->post(["param1", "param2"])) {
      $values = $request->getValues();
      ...
      # Su código aquí para una petición válida.
   }
   ```

   **`get()`:** permite validar que los parámetros de una petición hecha mediante el método `GET` sean válidos.

   **Descripción:**

   ```php
   DLRequest::get(array $parameters): bool
   ```

   **Uso:**

   ```php
   $request = new DLRequest;

   if ($request->get([
      'param1' => true,
      'param2' => false
   ])) {
      # Su código aquí para una petición válida.
   }
   ```

   Cuando se pasa un _array_ como argumento de la función `get` de la forma:

   ```php
   Array(
      'param1' => true,
      'param2' => false
   )
   ```

   Se indica que el contenido de `param1` es requerido, mientras que en `param2` el contenido es opcional.

   **`any()`:** permite validar la petición si coinciden con cualquiera de los parámetros indicados en el _array_ que se pase como argumento.

   **Descripción:**

   ```php
   DLRequest::any(array $parameters): bool
   ```

   **Uso:**

   ```php
   $request = new DLRequest;

   if ($request->any(["param1", "param2"])) {
      # Escriba su código aquí
   }
   ```
