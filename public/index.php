<?php

use DLTools\Auth\DLAuth;
use DLTools\Test\TestController;

ini_set('display_errors', 1);

/**
 * Tiempo de expiración de la sesión expresado en segundos.
 * 
 * @var int $sessionExpire
 */

use DLRoute\Requests\DLRoute;
use DLTools\Compilers\DLView;
use DLTools\Database\DLDatabase;
use DLTools\Database\Model;
use DLTools\Database\ParseSQL;

$sessionExpirte = time() + 1300;

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

DLRoute::get('/', function (object $params) {
    return view('test', [
        "basedir" => '',
        "authenticted" => 1,
        "files" => '',
        "uploads" => '',
        "info" => '',
        "query" => '',
        "username" => '',
        "token" => '',
        "isValid" => ''
    ]);
});

$auth = DLAuth::get_instance();

$auth->logged(function () {
    DLRoute::get('/ciencia', function (object $params) {
        return $params;
    });
});

DLRoute::get('/test/{page}/{rows}', [TestController::class, 'test'])->filter_by_type([
    "page" => "integer",
    "rows" => "integer"
]);

DLRoute::post('/file', [TestController::class, 'file']);

final class Employee extends Model {
}

DLRoute::get('/sql', function () {

    $query = Employee::where('employee_date', 'is null')->paginate(1, 3);

    return [
        "query" => $query
    ];
});

DLRoute::execute();
