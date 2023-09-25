<?php

namespace DLTools\HttpRequest;

/**
 * Permite procesar peticiones. Para obtener una instancia de esta
 * clase debes escribir la siguiente línea:
 * 
 * ```
 * $request = DLRequest::getInstance();
 * ```
 * @package DLTools
 * 
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @version v1.0.0 (2022-06-01) - Initial release
 * @license MIT
 */
class DLRequest {
    /**
     * Instancia única de la clase DLRequest
     *
     * @var self|null
     */
    private static ?self $instance = NULL;

    private bool $recaptcha = false;

    private function __construct() {
    }

    /**
     * Devuelve el método de envío de la petición
     *
     * @return string
     */
    public function getMethod(): string {
        return trim($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Funciona de la misma forma que GET, excepto que lo hace utilizando el método POST.
     *
     * @param array $param Campos del formulario
     * @return boolean
     */
    public function post(array $param, ?callable $callback = NULL): bool {

        if ($this->getMethod() !== "POST" || !(count($_REQUEST) > 0)) {
            return false;
        }

        $param['csrf-token'] = true;

        return $this->validate($param);
    }

    /**
     * Valida cualquier solicitud realizada mediante el protocolo HTTP
     *
     * @param array $parametros Arreglo asociativo que contiene los nombres y las restricciones de los parámetros esperados en la solicitud
     * @return bool Retorna verdadero si se pasan todas las validaciones y falso en caso contrario
     */
    private function validate(array $params): bool {
        $data = $this->getMethod() === "POST"
            ? $_POST
            : $_GET;

        foreach ($data as $paramName => $value) {
            if (!array_key_exists($paramName, $params)) {
                return false;
            }

            if ($params[$paramName] && empty(trim($value))) {
                return false;
            }
        }

        return true;
    }


    /**
     * Devuelve un array par clave y valor con los campos del formulario
     * con sus respectivos valores.
     * 
     * Por ejemplo:
     * 
     * ```
     * $data = $request->get_values();
     * ```
     *
     * @return array
     */
    public function get_values(): array {
        if ($this->getMethod() === "POST") {
            return $_POST;
        }

        return $_GET;
    }

    /**
     * Valida una petición que utiliza el método GET.
     * 
     * Por ejemplo:
     * 
     * ```
     * $form = [
     *  "name" => true,
     *  "message" => false
     * ];
     * 
     * if ($request->get($form)) {
     *      $data = $request->get_values();
     *  ...
     * }
     * ```
     * Donde `name` y `lastname` es el nombre del campo del formulario
     * y `true` o `false` indica si es requerido o no. 
     * 
     *
     * @param array $param Parámetros o campos del formulario
     * @return boolean
     */
    public function get(array $param): bool {
        if ($this->getMethod() !== "GET" || !(count($_REQUEST) > 0)) {
            return false;
        }

        return $this->validate($param);
    }

    /**
     * Evalúa si el usuario está en la página de inicio.
     * 
     * @return bool
     */
    public function isHome(): bool {
        return count($_GET) === 0;
    }

    /**
     * Permite que una acción se ejecute en cualquiera de los módulos seleccionados
     *
     * Ejemplo de código:
     * ```
     * $modules = [
     *  "blog", "cine", "new"
     * ];
     * 
     * if ($request->modules($modules)) {
     *  // Acción que se ejecutará en los módulos donde coincidan
     * }
     * ```
     * 
     * @param array $modules Módulo donde se requiere que se realice una acción.
     * @return boolean
     */
    public function modules(array $modules): bool {
        $data = $_REQUEST;

        foreach ($modules as $key) {
            if (array_key_exists($key, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Devuelve una única instancia de la clase `DLRequest`
     *
     * @return self
     */
    public static function get_instance(): self {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Procesa los datos enviados por el formulario
     *
     * @param array $param
     * @param callable $callback
     * @return void
     */
    public function processFormData(array $param, callable $callback): void {
        $method = $this->getMethod();

        $param['csrf-token'] = true;

        $recaptcha = $this->getRecaptcha();

        if ($recaptcha) {
            $param['g-recaptcha-response'] = true;
        }

        if ($method === "POST" && $this->post($param)) {
            $callback((object) $this->get_values());
        }
    }

    /**
     * Indica que se estableció un reCAPTCHA en el formulario del
     * usuario.
     *
     * @param boolean $recaptcha
     * @return void
     */
    public function setRecaptcha(bool $recaptcha = false): void {
        $this->recaptcha = $recaptcha;
    }

    /**
     * Devuelve `true` o `false` para indicar si se debe establecer reRECAPTCHAS
     *
     * @return boolean
     */
    public function getRecaptcha(): bool {
        return $this->recaptcha;
    }
}