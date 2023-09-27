<?php

namespace DLRoute\Config;

use DLTools\Config\Credentials;

trait DLCredentials {

    /**
     * @var array
     */
    protected array $environments = [];

    /**
     * Devuelve las variables de entorno en un array de objetos
     *
     * @return array
     */
    protected function get_environments(): array {
        return $this->environments;
    }

    /**
     * Devuelve las variables de entorno como objetos.
     *
     * @return object
     */
    protected function get_environments_as_object(): object {
        /**
         * Variables de entorno
         * 
         * @var array
         */
        $environments = $this->get_environments();

        /**
         * Variables con tipos establecidos.
         * 
         * @var array
         */
        $vars = [];

        foreach ($environments as $environment) {

            if (!is_object($environment)) {
                continue;
            }

            /**
             * Tipo de datos.
             * 
             * @var string
             */
            $type = $environment->type ?? null;

            /**
             * Valor de la variable de entorno que será analizado en
             * función de su tipo establecido.
             * 
             * @var string
             */
            $value = $environment->value ?? null;

            /**
             * Nombre de la variable de entorno.
             * 
             * @var ?string $var_name
             */
            $var_name = $environment->variable ?? null;

            if (is_null($var_name)) {
                continue;
            }

            if ($type === "boolean") {
                $value = $this->parse_boolean($value);
            }

            if ($type === "integer") {
                $value = $this->parse_integer($value);
            }

            if ($type == "float") {
                $value = $this->parse_float($value);
            }

            if ($type === "string") {
                $value = $this->parse_string($value);
            }

            if (is_numeric($value) && is_null($type)) {
                $value = $this->parse_numeric($value);
            }

            if ($value === "true" && is_null($type)) {
                $value = true;
            }

            if ($value === "false" && is_null($type)) {
                $value = false;
            }

            $vars[$var_name] = [
                "type" => $type,
                "value" => $value
            ];
        }

        return (object) $vars;     
    }

    /**
     * Devuelve las credenciales críticas de la aplicación.
     *
     * @return Credentials
     */
    protected function get_credentials(): Credentials {
        /**
         * Veritica primero si está vacía las variables de entorno.
         * 
         * @var boolean
         */
        $is_empty = empty(
            $this->get_environments()
        );

        if ($is_empty) {
            $this->parse_file();
        }

        return Credentials::get_instance(
            $this->get_environments_as_object()
        );
    }

    /**
     * Parsea la cadena `true` o `false` como un booleano.
     * 
     * @param string $input Valor a ser analizado.
     * @return boolean
     */
    private function parse_boolean(string $input): bool {
        return $input === "true" ? true : false;
    }

    /**
     * Convierte en un entero una cadena marcada previamente como entero.
     *
     * @param string $input Valor a ser analizado
     * @return integer
     */
    private function parse_integer(string $input): int {
        return (int) $input;
    }

    /**
     * Conviertie en un número de punto flotante a una cadena marcada como flotante.
     *
     * @param string $input Entrada a ser analizada
     * @return float
     */
    private function parse_float(string $input): float {
        return (float) $input;
    }

    /**
     * Convierte a un número real o entero a una cadena marcada como `numeric`
     *
     * @param string $input Entrada a ser analizadas
     * @return void
     */
    private function parse_numeric(string $input): float | int {
        /**
         * Patrón de búsqueda de números de punto flotante. De no ser
         * un número de punto flotante, se considerará un entero, ya que
         * previamente había sido marcado como númérico por el analizador sintáctico.
         * 
         * @var string
         */
        $pattern = "/^[0-9]+\.[0-9]+$/";

        /**
         * Indicador de número flotante o no.
         * 
         * @var boolean
         */
        $is_float = preg_match($pattern, $input);

        if ($is_float) {
            return (float) $input;
        }

        return (int) $input;
    }

    /**
     * Remueve al principio y al final las comillas establecidas en las
     * cadenas de texto dentro de las variables de entorno, a la vez, que quita
     * los espacios sobrantes que hayan quedado.
     *
     * @param string $input
     * @return string
     */
    private function parse_string(string $input): string {
        $input = trim($input, "\"\'\`");
        $input = trim($input);

        return $input;
    }
}