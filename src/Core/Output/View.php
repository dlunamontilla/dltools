<?php

declare(strict_types=1);

namespace DLTools\Core\Output;

use DLTools\Compilers\DLView;

/**
 * Clase View
 * 
 * Se encarga de la gestión de vistas en formato HTML dentro del sistema.
 * Extiende de DLView para aprovechar su funcionalidad de compilación y renderizado.
 * 
 * @package DLTools\Core\Output
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2025 David E Luna M
 * @license MIT
 */
final class View extends DLView {

    /**
     * Constructor de la clase View.
     * 
     * Inicializa la vista heredando la configuración y métodos de DLView.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene el contenido renderizado de una vista en formato HTML.
     * 
     * @param string $view Nombre de la vista o ruta de la vista.
     *     - La ruta por defecto es `welcome`.
     *     - La estructura de rutas puede utilizar barras diagonales `/` o puntos `.` para la separación.
     * @param array $options Un array asociativo que contiene las variables a ser inyectadas en la vista.
     * @return string Contenido HTML generado a partir de la vista.
     */
    public static function get(string $view = 'welcome', array $options = []): string {
        new self();

        ob_start();
        self::load($view, $options);

        /**
         * Contenido obtenido de la vista tras la carga y renderizado.
         * 
         * @var string
         */
        $content = (string) ob_get_clean();

        return trim($content);
    }
}
