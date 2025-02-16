<?php

namespace DLTools\Config;

use DLRoute\Config\Controller;

/**
 * Tipos de datos de las variables de entorno.
 * 
 * @package DLTools\Config;
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
trait DLVarTypes {

    protected static array $types = [
        "string",
        "numeric",
        "integer",
        "uuid",
        "email",
        "float",
        "boolean",
        "null"
    ];


    /**
     * Indica si un valor es de tipo string
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_string(string $value): bool {
        $value = trim($value);

        /**
         * Patrón de búsqueda
         * 
         * @var string
         */
        $pattern = "/^(\"(.*?)\"|\'(.*)\'|\`(.*)\`)$/";

        return preg_match($pattern, $value);
    }

    /**
     * Indica si un valor es de tipo `true`
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_true(string $value): bool {
        $value = trim($value);
        return $value === "true";
    }

    /**
     * Indica si un valor es de tipo `false`
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_false(string $value): bool {
        $value = trim($value);
        return $value === "false";
    }

    /**
     * Indica si un valor es de tipo `boolean`
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_boolean(string $value): bool {
        return $this->is_true($value) || $this->is_false($value);
    }

    /**
     * Indica si un valor es de tipo `ìnteger`
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_integer(string $value): bool {
        $value = trim($value);
        return preg_match("/^(-{0,1}[0-9]+)$/", $value);
    }

    /**
     * Indica si un valor es de tipo `float`
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_float(string $value): bool {
        $value = trim($value);

        /**
         * Patrón de búsqueda de númeoros reales con decimales.
         * 
         * @var string
         */
        $pattern = "/^(-{0,1}[0-9]+\.(?=[0-9]+)[0-9]+)$/";

        return preg_match($pattern, $value);
    }

    /**
     * Indica si un valor es de tipo `numeric`. Esto significa que el valor puede ser
     * un número entero o de punto flotante.
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_numeric(string $value): bool {
        return $this->is_integer($value) || $this->is_float($value);
    }

    /**
     * Indica si un valor es de tipo `null`
     *
     * @param string $value Valor a ser analizado
     * @return boolean
     */
    protected function is_null(string $value): bool {
        $value = trim($value);
        $value = strtolower($value);

        return $value === "null" || is_null($value);
    }

    /**
     * Indica si un valor es de tipo `email`
     *
     * @param string $input Valor a ser analizado
     * @return boolean
     */
    protected function is_email(string $input): bool {
        /**
         * Valor a ser analizado.
         * 
         * @var string
         */
        $value = trim($input);

        if (strlen($input) < 5) {
            return false;
        }

        /**
         * Patrón de búsqueda de correo electrónico.
         * 
         * @var string
         */
        $email_pattern = '/^[a-z0-9]+[a-z][a-z0-9-_.]{1,63}\@[a-z][a-z0-9-_]+\.[a-z][a-z0-9-.]{1,10}$/i';

        /**
         * Resultado de un análisis previo hecho para validar un correo electrónico.
         * 
         * @var boolean
         */
        $found = preg_match($email_pattern, $input);

        if (!$found) {
            return false;
        }

        /**
         * Si es `true` significa que hay más de 1 punto segundos.
         * 
         * @var boolean
         */
        $points = preg_match('/([-_.]{2,})/', $input);

        if ($points) {
            return false;
        }

        if (preg_match("/[^a-z]$/", $input)) {
            return false;
        }

        return true;
    }

    /**
     * Determina si la entrada es una cadena UUID válida
     *
     * @param mixed $input entrada a ser analizada
     * @return boolean
     */
    protected function is_uuid(mixed $input): bool {

        if (!is_string($input)) {
            $input = "";
        }

        $input = trim($input);

        /**
         * Patrón de búsqueda del identificador UUID.
         * 
         * @var string
         */
        $pattern = "/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/";

        return preg_match($pattern, $input);
    }
}
