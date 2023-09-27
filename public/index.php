<?php
use DLTools\Auth\DLAuth;
ini_set('display_errors', 1);

/**
 * Tiempo de expiración de la sesión expresado en segundos.
 * 
 * @var int $sessionExpire
 */

use DLRoute\Requests\DLRoute;
use DLTools\Compilers\DLView;
use DLTools\Test\TestController;

$sessionExpirte = 1300;

session_set_cookie_params($sessionExpirte);
session_start();

include dirname(__DIR__, 1) . "/vendor/autoload.php";

/**
 * Devuelve código HTML a partir de una vista.
 *
 * @param string $view Vista a ser procesada.
 * @param array $options Variables para la vista.
 * @return string
 */
function view(string $view, array $options = []): string {
    DLView::getInstance();

    ob_start();
    DLView::load($view, $options);
    
    /**
     * Contenido obtenido de la vista.
     * 
     * @var string
     */
    $content = (string) ob_get_clean();

    return trim($content);
}

DLRoute::get('/', function(object $params) {
    return view('test');
});

DLRoute::get('/files', function() {
    return view('files');
});

DLRoute::post('/files', [TestController::class, 'index']);

DLRoute::get('/get/classname', function(): array {
    /**
     * Clase con su nombre de espacios.
     * 
     * @var string
     */
    $namespace = TestController::class;

    /**
     * Partes de la clase.
     * 
     * @var array
     */
    $parts = explode("\\", $namespace);

    /**
     * Nombre de la clase sin el nombre de espacios.
     * 
     * @var string
     */
    $classname = end($parts);

    /**
     * Indica si se encontró alguna coincidencia.
     * 
     * @var boolean
     */
    $found = preg_match_all('/[A-Z][a-z]+/', $classname, $match);

    $table = "";

    if ($found) {
        $table = implode("_", $match[0]);
        $table = strtolower($table);
    }
    return [
        "class" => $classname,
        "parts_name" => $found ? $match[0] : [],
        "tablaSQL" => $table
    ];
});

DLRoute::get('/user/get', [TestController::class, 'get_users']);

DLRoute::get('/see/clients', [TestController::class, 'clients_form']);
DLRoute::post('/see/clients', [TestController::class, 'save_clients']);

# Definción de rutas de productos.
DLRoute::get('/product/see', [TestController::class, 'see_products']);
DLRoute::post('/product/create', [TestController::class, 'create_product']);

# Probando las variables de Entorno
DLRoute::get('/vars', [TestController::class, 'test_config']);

DLRoute::post('/user/test', [TestController::class, 'users_test']);

# Envío de correos electrónicos:
DLRoute::post('/mail', [TestController::class, 'mail']);

# Prueba con la plantillas
DLRoute::get('/template', [TestController::class, 'template']);

DLRoute::execute();