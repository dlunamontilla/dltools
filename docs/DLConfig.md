# DLConfig

La clase `DLConfig` permite obtener a partir de una variable de entorno (`.env`) los parámetros de conexión, así como cualquier credencial asociado a una API.

1. **Constructor:** durante el instanciamiento de la clase `DLConfig` se leen las variables de entorno.

   ```php
   DLConfig::__construct(string $path = '/.env');
   ```

   Se llama al momento de instanciar la clase:

   ```php
   $request = new DLConfig;
   ```

2. **`DLConfig::getCredentials(): object`:** devuelve un un objeto con las credenciales establecidas en `.env`.

   **Sintaxis:**

   ```php
   $config = new DLConfig;
   $credentials = $config->getCredentials();
   ```

3. **`DLConfig::getPDO(): PDO`:** Devuelve un objeto `PDO` con los parámetros de conexión extablecidos en la variable de entorno (`.env`).

    **Sintaxis:**

    ```php
    $config = new DLConfig;
    $pdo = $config->getPDO();
    ```