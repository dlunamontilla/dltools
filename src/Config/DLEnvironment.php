<?php

namespace DLTools\Config;

use DLRoute\Config\DLCredentials;
use DLRoute\Requests\DLOutput;
use DLRoute\Server\DLServer;

/**
 * Lee e interpreta el archivo .env, pero con tipado estático.
 * 
 * @package DLTools\Config
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
trait DLEnvironment {
    
    use DLCredentials;
    use DLVarTypes;

    /**
     * Variables de entorno usada por la aplicacion.
     *
     * @var array
     */
    private array $vars = [];

    /**
     * Devuelve el contenido del archivo .env
     *
     * @return string
     */
    private function get_env(): string {
        /**
         * Directorio raíz de la aplicación.
         * 
         * @var string
         */
        $root = DLServer::get_document_root();

        /**
         * Variables de entorno.
         * 
         * @var string
         */
        $filename = "{$root}/.env.type";

        if (!file_exists($filename)) {
            return "";
        }

        /**
         * Contenido del archivo que contiene las variables de entorno.
         * 
         * @var string
         */
        $content = file_get_contents($filename);

        return trim($content);
    }

    /**
     * Analiza y parsea el contenido del archivo `.env.type`
     *
     * @return void
     */
    protected function parse_file(): void {
        /**
         * Indica si contiene variables de entorno.
         * 
         * @var boolean
         */
        $contains_environment = !empty(
            $this->get_environments()
        );

        if (!$contains_environment) {
            /**
             * Contenido de la variable de entorno.
             * 
             * @var string|null
             */
            $content = $this->get_env();
    
            $this->remove_comments($content);
    
            /**
             * Líneas
             * 
             * @var string[]
             */
            $lines = explode("\n", $content);
    
            foreach ($lines as $line) {
                $line = trim($line);
    
                if (empty($line)) {
                    continue;
                }
    
                $this->parse_line($line);
            }
        }

        /**
         * Variables de entorno.
         * 
         * @var array
         */
        $environments = $this->get_environments();

        foreach ($environments as $environment) {
            if (!isset($environment->variable)) {
                continue;
            }
            
            putenv("{$environment->variable}={$environment->value}");
        }

        if (empty($environments)) {
            /**
             * Se utilizará en el caso de que no se haya definido un archivo .env.type o esté vacío. Esto
             * aplica para aquellos servidores donde no puedas utilizar .env.type.
             * 
             * Lo que se cargue aquí, son las credenciales mínimas.
             */
            $environments = [
                [
                    "value" => getenv('DL_PRODUCTION')
                ],
    
                [
                    "value" => getenv('DL_DATABASE_HOST')
                ],
    
                [
                    "value" => getenv('DL_DATABASE_PORT')
                ],
    
                [
                    "value" => getenv('DL_DATABASE_USER')
                ],
                
                [
                    "value" => getenv('DL_DATABASE_PASSWORD')
                ],
    
                [
                    "value" => getenv('DL_DATABASE_NAME')
                ],
                
                [
                    "value" => getenv('DL_DATABASE_CHARSET') ?? 'utf8'
                ],
                
                [
                    "value" => getenv('DL_DATABASE_COLLATION') ?? 'utf8_general_ci'
                ],
                
                [
                    "value" => getenv('DL_DATABASE_DRIVE') ?? 'mysql'
                ],
                
                [
                    "value" => getenv('DL_PREFIX') ?? 'dl_'
                ],
            ];

            $this->environments = $environments;
        }
    }

    /**
     * Analiza cada línea
     *
     * @param string $line
     * @return void
     */
    private function parse_line(string $line): void {

        /**
         * Partes de una línea
         * 
         * @var array
         */
        $parts = explode("=", $line);

        /**
         * Nombre de la variable. Incluye su tipo.
         * 
         * @var string $var_name
         */
        $var_name = array_shift($parts);

        /**
         * Valor de la variable. Si el valor de la variable no coincide con su tipo
         * se producirá un error en la lectura de la variable de entorno.
         * 
         * @var string $value
         */
        $value = array_shift($parts);

        /**
         * Información de la variable
         * 
         * @var object $var_info
         */
        $var_info = $this->process_varname($var_name);

        /**
         * Información del valor de la variable
         * 
         * @var object
         */
        $value_info = $this->process_value($value, $var_info);

        $this->environments[] = $value_info;
    }

    /**
     * Procesa y valida los nombres de variables.
     *
     * @param string $var_name Nombre de variable a ser procesada.
     * @return object Información devuelta en un objeto de la variable
     */
    private function process_varname(string $var_name): object {
        /**
         * Partes de una variable con tipado estático.
         * 
         * @var string[] $parts
         */
        $parts = explode(":", $var_name);

        /**
         * Nombre de la variable
         * 
         * @var string $var
         */
        $var = array_shift($parts);

        /**
         * Tipo de datos de la variable.
         * 
         * @var string
         */
        $type = array_shift($parts);

        $var = trim($var);
        $type = trim($type);

        /**
         * Patrón de búsqueda de las variables de entorno.
         * 
         * @var string
         */
        $pattern_varname = "/^[A-Z][A-Z0-9_]+$/";

        /**
         * Patrón de búsqueda de tipos permitidos.
         * 
         * @var string
         */
        $pattern_type = "/^[a-z]+$/";

        if (!preg_match($pattern_varname, $var)) {
            static::error("El formato de la variable {$var} es inválido");
        }

        if (!preg_match($pattern_type, $type)) {
            static::error("Formato de tipo «{$type}» es inválido");
        }

        $type = strtolower($type);

        if (!in_array($type, self::$types)) {
            $type = strtoupper($type);
            static::error("Tipo de datos desconocido. Tipo desconocido {$type}");
        }

        return (object) [
            "var" => $var,
            "type" => $type
        ];
    }

    /**
     * Procesa y valida el valor a partir de la información de la variable.
     * 
     * @param string $value Valor a ser procesado.
     * @param object $var_info Información de la variable
     * @return object Valor devuelto con su tipo especificado.
     */
    private function process_value(string $value, object $var_info): object {
        $value = trim($value);

        /**
         * Información del valor de la variable.
         * 
         * @var array $value_info
         */
        $value_info = [];

        /**
         * Tipo de datos capturados en la variable.
         * 
         * @var string
         */
        $type = $var_info->type;

        /**
         * Nombre de la variable de entorno.
         * 
         * @var string
         */
        $var_name = $var_info->var;

        /**
         * Nombre del método de evaluación de tipo en función del tipo
         * de datos a evaluar.
         * 
         * @var string
         */
        $method_name = "is_{$type}";

        if (!method_exists($this, $method_name)) {
            static::error("Tipo desconocido: «{$type}»");
        }

        /**
         * Determina si el tipo a evaluar corresponde al valor asignado
         * a la variable de entorno.
         * 
         * @var boolean
         */
        $is_valid = $this->{$method_name}($value);

        if (!$is_valid) {
            $this->error_type("El tipo de dato '{$type}' definido para la variable '{$var_name}' no es compatible con el valor asignado '{$value}'.");
        }

        $value_info = [
            "variable" => $var_name,
            "type" => $type,
            "value" => $value
        ];

        return (object) $value_info;
    }

    /**
     * Remueve todos los comentarios del archivo `.env.type`
     *
     * @param string $content Contenido a ser depurado
     * @return void
     */
    private function remove_comments(string &$content): void {
        $content = trim($content);
        $content = preg_replace("/\#{1}(.*)|\/{2}(.*)/", '', $content);
        $content = preg_replace("/\/\*[\s\S]*?\*\//", "", $content);
    }

    /**
     * Devuelve un mensaje de error.
     *
     * @param string $message Mensaje personalizado
     * @return void
     */
    private static function error(string $message): void {
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
    private function error_type(string $message): void {
        header("Content-Type: application/json; charset=utf-8", true, 500);

        echo DLOutput::get_json([
            "status" => false,
            "error" => "Tipo de datos incompatible",
            "details" => $message
        ], true);

        exit;
    }
}
