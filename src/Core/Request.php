<?php

declare(strict_types=1);

namespace Core\Request;

use DLRoute\Requests\DLRequest;
use DLTools\Config\DLValues;

/**
 * Procesa la petición del cliente HTTP.
 *
 * Esta clase extiende de DLRequest y se encarga de gestionar los valores enviados
 * en la petición, ya sean en formato de arreglo o cadena, permitiendo el acceso al
 * contenido completo de la misma. Implementa el patrón Singleton para asegurar que
 * solo exista una instancia en tiempo de ejecución.
 *
 * @package Framework\Requests
 * @author 
 * @copyright 
 * @license MIT
 */
final class Request extends DLRequest {

    use DLValues;

    /**
     * Instancia única de la clase (Patrón Singleton).
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Contenido completo de la petición HTTP.
     *
     * @var string
     */
    private string $content = "";

    /**
     * Constructor privado que inicializa la instancia de Request.
     *
     * Se invoca la función get_values() para obtener los datos de la petición. Si los
     * valores son un arreglo, se asignan a la propiedad estática correspondiente; si
     * son una cadena, se almacenan en la propiedad $content.
     */
    private function __construct() {
        /**
         * Valores de la petición (pueden ser un arreglo o una cadena).
         *
         * @var string|array $values
         */
        $values = $this->get_values();

        if (is_array($values)) {
            self::$values = $values;
        }

        if (is_string($values)) {
            $this->content = $values;
        }
    }

    /**
     * Obtiene la instancia única de Request.
     *
     * Implementa el patrón Singleton para garantizar que solo exista una instancia
     * de la clase durante el ciclo de vida de la aplicación.
     *
     * @return self Instancia de Request.
     */
    public static function get_instance(): self {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retorna el contenido enviado en la petición HTTP.
     *
     * El método devuelve el contenido completo enviado por el cliente, eliminando
     * espacios en blanco al inicio y al final de la cadena.
     *
     * @return string Contenido de la petición.
     */
    public function get_content(): string {
        return trim($this->content);
    }
}
