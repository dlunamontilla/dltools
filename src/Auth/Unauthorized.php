<?php

namespace DLTools\Auth;
use DLRoute\Server\DLServer;

/**
 * Envía un mensaje de error con códigos de estados.
 * 
 * @package DLTools\Auth
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
final class Unauthorized {

    /**
     * Salida que indica que el usuario no se encuentra autorizado para realizar peticiones
     * en las rutas marcadas como autenticadas si el usuario no se encuentra autenticado.
     *
     * @return array
     */
    public function unauthorized(): array {

        return [
            "code" => 401,
            "error" => "No se encuentra autorizado para acceder a esta ruta.",
            "route" => DLServer::get_route(),
            "ip" => DLServer::get_ipaddress()
        ];
    }

    /**
     * Rutas a las que no se les tienen permitido acceder a los usuarios que se encuentran autenticados
     *
     * @return array
     */
    public function forbidden(): array {

        return [
            "code" => 403,
            "error" => "Prohibido el acceso a esta ruta.",
            "route" => DLServer::get_route(),
            "ip" => DLServer::get_ipaddress()
        ];
    }
}