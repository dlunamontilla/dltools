<?php

declare(strict_types=1);

namespace DLTools\Core\Traits;

use Exception;

trait Token {

    /**
     * Genera y retorna un Identificador Único Universal (UUID) versión 4.
     *
     * Esta función crea un UUID v4 basado en números aleatorios, siguiendo la especificación RFC 4122.
     *
     * @return string El UUID generado en el formato estándar (8-4-4-4-12).
     * @throws Exception Si la generación de bytes aleatorios falla.
     */
    public function generate_uuid(): string {
        // Genera 16 bytes aleatorios para construir la base del UUID.
        $data = random_bytes(16);

        // Configura los bits correspondientes a la versión (4) y variante (RFC 4122).
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versión 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variante RFC 4122

        // Convierte los bytes en el formato estándar de UUID.
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        return $uuid;
    }

    /**
     * Retorna el token CSRF almacenado en la sesión o genera uno nuevo si es inválido.
     *
     * Este método verifica la existencia y validez del token CSRF en la sesión. Si no se encuentra 
     * o su longitud no coincide con el valor esperado, genera uno nuevo utilizando `get_token()`, 
     * lo almacena en la sesión y lo retorna.
     *
     * @param int $length Opcional. Longitud esperada del token CSRF. Por defecto, 100 caracteres.
     * @return string El token CSRF válido.
     */
    public function get_csrf_token(int $length = 100): string {
        /** @var string|null $csrf_token */
        $csrf_token = $_SESSION['csrf_token'] ?? null;

        if (!is_string($csrf_token) || strlen(trim($csrf_token)) !== $length) {
            $csrf_token = $this->get_token(length: $length);
            $_SESSION['csrf_token'] = $csrf_token;
        }

        return $csrf_token;
    }


    /**
     * Genera y retorna un token aleatorio en formato hexadecimal.
     *
     * La función utiliza un conjunto de bytes aleatorios cuya cantidad se especifica en el parámetro $length,
     * y posteriormente convierte estos bytes a una representación hexadecimal.
     *
     * @param int $length Longitud en bytes a generar (por defecto, 100).
     * @return string El token generado en formato hexadecimal.
     * @throws Exception Si falla la generación de bytes aleatorios.
     */
    public function get_token(int $length = 100): string {
        /** @var string $bytes */
        $bytes = random_bytes(length: $length);

        /** @var string $token */
        $token = bin2hex($bytes);

        return $token;
    }

    /**
     * Cifra la contraseña proporcionada utilizando el algoritmo Argon2id.
     *
     * Este método recibe una contraseña en texto plano y aplica el algoritmo Argon2id con una
     * configuración personalizada, generando un hash seguro de la misma.
     *
     * @param string $password La contraseña en texto plano que se desea cifrar.
     * @return string El hash resultante de la contraseña.
     * @throws Exception Si ocurre un error durante el proceso de cifrado.
     */
    public function get_password_hash(string $password): string {
        $config = [
            "memory_cost" => 131072,
            "time_cost"   => 4,
            "threads"     => 2
        ];

        /** @var string $hash */
        $hash = password_hash($password, PASSWORD_ARGON2ID, $config);

        if ($hash === false) {
            throw new Exception("Error al cifrar la contraseña.");
        }

        return $hash;
    }

    /**
     * Verifica si una contraseña coincide con su hash almacenado.
     *
     * Este método utiliza la función `password_verify()` para comprobar si la contraseña 
     * en texto plano coincide con el hash previamente generado.
     *
     * @param string $password La contraseña en texto plano proporcionada por el usuario.
     * @param string $hash El hash almacenado de la contraseña.
     * @return bool `true` si la contraseña es válida, `false` en caso contrario.
     */
    public function validate_password(string $password, string $hash): bool {
        return password_verify(password: $password, hash: $hash);
    }
}
