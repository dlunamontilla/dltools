<?php

namespace DLTools\Config;

use DLRoute\Requests\DLOutput;

trait DLValues {

    use DLVarTypes;

    /**
     * Valores de los parámetros de la petición, incluyendo, formato JSON
     *
     * @var array
     */
    protected static array $values = [];

    /**
     * Devuelve el valor de un campo seleccionado.
     *
     * @param string $name Campo seleccionado por el usuario
     * @return string
     */
    public function get_input(string $name): mixed {

        if (!array_key_exists($name, static::$values)) {
            return null;
        }

        /**
         * Entrada del usuario.
         * 
         * @var mixed
         */
        $value = static::$values[$name] ?? null;
        return $value;
    }

    /**
     * Se asegura que la entrada del usuario sea un correo electrónico, de lo contrario,
     * de volverá un error de tipo incompatible.
     *
     * @param string $field Nombre del campo.
     * @return string
     */
    public function get_email(string $field): string {
        /**
         * Entrada del usuario.
         * 
         * @var string|null
         */
        $value = static::$values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_null($value) || !($this->is_email($value))) {
            $this->invalid_type("«{$value}» No es un formato válido de correo en el campo «{$field}»");
        }

        return $value;
    }

    /**
     * Devuelve una cadena UUID a partir de la entrada de un usuario. Si la cadean
     * `UUIDv4` es inválido, devolverá un error de tipo.
     *
     * @param string $field Campo del formulario
     * @return string
     */
    public function get_uuid(string $field): string {
        /**
         * Entrada del usuario.
         * 
         * @var string|null
         */
        $value = static::$values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_null($value)) {
            $this->invalid_type("El campo {$field} es requerido");
        }

        if (!($this->is_uuid($value))) {
            $this->invalid_type("{$value} es un UUID inválido");
        }

        return $value;
    }

    /**
     * Devuelve un valor numérico, sea entero, o de punto flotante.
     *
     * @param string $field Campo del formulario
     * @return float | int
     */
    public function get_numeric(string $field): float | int {

        /**
         * Entrada del usuario.
         * 
         * @var int|float|string|null
         */
        $value = static::$values[$field] ?? '';

        if (!is_numeric($value)) {
            $value = '';
        }

        $value = trim((string) $value);

        $is_valid = $this->is_numeric($value);

        if (!$is_valid) {
            $this->invalid_type("Se esperaba un valor numérico en {$field}, en lugar de {$value}");
        }

        if ($this->is_integer($value)) {
            return (int) $value;
        }

        return (float) $value;
    }

    /**
     * Devuelve un entero a partir de la entrada del usuario.
     *
     * @param string $field Campo del formulario
     * @return integer
     */
    public function get_integer(string $field): int {

        /**
         * Entrada de usuario.
         * 
         * @var string|int|null
         */
        $value = static::$values[$field] ?? null;

        if (!is_numeric($value)) {
            $value = '';
        }

        $value = trim((string) $value);

        if (!($this->is_integer($value))) {
            $this->invalid_type("Se esparaba un tipo entero en {$field} en lugar de {$value}");
        }

        return (int) $value;
    }

    /**
     * Devuelve un número de punto flotante a partir de la entrada del usuario.
     *
     * @param string $field Campo del formulario
     * @return float
     */
    public function get_float(string $field): float {

        /**
         * Entrada de usuario.
         * 
         * @var string|float|null
         */
        $value = static::$values[$field] ?? null;

        if (!is_numeric($value)) {
            $value = '';
        }

        $value = trim((string) $value);

        if (!($this->is_float($value))) {
            $this->invalid_type("Se esperaba un número real en {$field} en lugar de {$value}");
        }

        return (float) $value;
    }

    /**
     * Devuelve un valor booleano a partir de la entrada del usuario.
     *
     * @param string $field Campo del formulario
     * @return boolean
     */
    public function get_boolean(string $field): bool {

        /**
         * Entrada de usuario
         * 
         * @var string|boolean|null
         */
        $value = static::$values[$field] ?? "false";

        if (!is_string($value)) {
            $value = "false";
        }

        $value = trim($value);
        $value = strtolower($value);

        if (!($this->is_boolean($value))) {
            $this->invalid_type("El valor proporcionado, '{$value}', no cumple con el formato esperado para un booleano en el campo '{$field}'");
        }

        return $value === "true";
    }

    /**
     * Devuelve como cadena de texto la entrada del usuario, en el caso de que se trate
     * de una cadena de texto.
     *
     * @param string $field
     * @return string
     */
    public function get_string(string $field): string {

        /**
         * Entrada de usuario.
         * 
         * @var string|null
         */
        $value = static::$values[$field] ?? null;

        if (!is_string($value)) {
            $this->invalid_type("Se esperaba una cadena de texto en {$field}");
        }

        $value = trim($value, "\"\'");
        $value = trim($value);

        return $value;
    }

    /**
     * Devuelve un array a partir de la entrada de un cliente HTTP
     *
     * @param string $key Clave o propiedad del objeto `JSON` enviado
     * @return array
     */
    public function get_array(string $key): array {

        /**
         * Entrada del usuario
         * 
         * @var array|string|null
         */
        $value = static::$values[$key] ?? null;

        if (!is_array($value)) {
            $this->invalid_type("Se esperaba un array en " . print_r($value, true));
        }

        return $value;
    }

    /**
     * Devuelve una entrada de usuario.
     *
     * @param string $field
     * @return mixed
     */
    public function get_required(string $field): mixed {

        /**
         * Entrada obligatoria del usuario.
         * 
         * @var mixed
         */
        $value = static::$values[$field] ?? null;

        if (is_null($value)) {
            $this->error_requirenment("El campo «{$field}» es requerido");
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ((is_string($value) || is_array($value)) && empty($value)) {
            $this->error_requirenment("El campo «{$field}» es requerido");
        }

        return $value;
    }

    /**
     * Solo valida la longitud de la contreña.
     *
     * @param string $field Campo del formulario
     * @param integer $length Opcional. Longitud del formulario
     * @return string
     */
    public function get_password_valid(string $field, int $length = 8): string {

        /**
         * Entrada de usuario
         * 
         * @var string|null $value
         */
        $value = static::$values[$field] ?? null;

        if (!is_string($value)) {
            $this->invalid_type("La contraseña debe ser una cadena texto");
        }

        $value = trim($value);

        /**
         * Longitud de la contraseña.
         * 
         * @var string $password_length
         */
        $password_length = strlen($value);

        if ($password_length < $length) {
            $this->error_requirenment("La longitud de la contraseña debe ser igual o mayor a {$length}");
        }

        return $value;
    }

    public function filter_by_pattern(string $field, string $pattern = "/[\s\S]*/"): string {

        /**
         * Entrada de usuario
         * 
         * @var string|null $value
         */
        $value = static::$values[$field] ?? null;

        if (!is_string($value)) {
            $this->invalid_type("Entrada inválida en {$field}");
        }

        $found = preg_match($pattern, $value);

        if (!$found) {
            $this->invalid_type("Entrada no válida en {$field}, no cumple con los criterios esperados");
        }

        return $value;
    }

    /**
     * Devuelve un mensaje de error.
     *
     * @param string $message Mensaje personalizado
     * @return void
     */
    private function error_requirenment(string $message): void {
        header("Content-Type: application/json; charset=utf-8", true, 422);

        echo DLOutput::get_json([
            "status" => false,
            "error" => trim($message),
        ], true);

        exit;
    }

    /**
     * Devuelve un error de tipo personalizado
     *
     * @param string $message Mensaje personalizado
     * @return void
     */
    private function invalid_type(mixed $message): void {
        header("Content-Type: application/json; charset=utf-8", true, 400);

        echo DLOutput::get_json([
            "status" => false,
            "error" => trim($message),
        ], true);

        exit;
    }
}
