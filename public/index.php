<?php

ini_set('display_errors', 1);

/**
 * Tiempo de expiración de la sesión expresado en segundos.
 * 
 * @var int $sessionExpire
 */

use DLRoute\Requests\DLRoute;
use DLTools\Core\Output\View;

$sessionExpirte = time() + 1300;

session_set_cookie_params($sessionExpirte);
session_start();

include dirname(__DIR__, 1) . "/vendor/autoload.php";

DLRoute::get('/', function () {
    return View::get();
});

DLRoute::execute();
