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
    protected array $values = [];

    /**
     * Devuelve el valor de un campo seleccionado.
     *
     * @param string $name Campo seleccionado por el usuario
     * @return string
     */
    public function get_input(string $name): mixed {
        
        if (!array_key_exists($name, $this->values)) {
            return null;
        }

        /**
         * Entrada del usuario.
         * 
         * @var mixed
         */
        $value = $this->values[$name] ?? null;
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
        $value = $this->values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_null($value) || !($this->is_email($value))) {
            $this->invalid_type($value);
        }

        return $value;
    }

    /**
     * Devuelve una cadena UUID a partir de la entrada de un usuario. Si la cadean
     * UUID es inválido, devolverá un error de tipo.
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
        $value = $this->values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        if (!($this->is_uuid($value))) {
            $this->invalid_type($value);
        }

        return $value;
    }

    /**
     * Devuelve un valor numérico, sea entero, o de punto flotante.
     *
     * @param string $field Campo del formulario
     * @return void
     */
    public function get_numeric(string $field): float | int {

        /**
         * Entrada del usuario.
         * 
         * @var int|float|string|null
         */
        $value = $this->values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        $is_valid = $this->is_integer($value) || $this->is_float($value);

        if (!$is_valid) {
            $this->invalid_type($value);
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
        $value = $this->values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        if(!($this->is_integer($value))) {
            $this->invalid_type($value);
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
        $value = $this->values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        if (!($this->is_float($value))) {
            $this->invalid_type($value);
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
        $value = $this->values[$field] ?? null;

        if (is_string($value)) {
            $value = trim($value);
        }

        if (!($this->is_boolean($value))) {
            $this->invalid_type($value);
        }

        return (bool) $value;
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
        $value = $this->values[$field] ?? null;

        if (gettype($value) !== "string") {
            $this->invalid_type($value);
        }

        $value = trim($value, "\"\'");
        $value = trim($value);

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
        $value = $this->values[$field] ?? null;

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
        $value = $this->values[$field] ?? null;

        if (!is_string($value)) {
            $this->invalid_type($value);
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
        $value = $this->values[$field] ?? null;
        
        if (!is_string($value)) {
            $this->invalid_type($value);
        }

        $found = preg_match($pattern, $value);

        if (!$found) {
            $this->invalid_type($value);
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
        header("Content-Type: application/json; charset=utf-8", true, 500);

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
        header("Content-Type: application/json; charset=utf-8", true, 500);

        echo DLOutput::get_json([
            "status" => false,
            "error" => "Tipo de datos incompatible",
            "details" => $message
        ], true);

        exit;
    }
}