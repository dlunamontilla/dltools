<?php

namespace DLTools\Interfaces;

use DLTools\Auth\DLAuthOptions;
use DLTools\Auth\DLCookie;
use DLTools\Auth\DLUser;

/**
 * Sistema de autenticación del sistema
 * 
 * @package DLTools\Interface
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
interface AuthInterface {

    /**
     * Devuelve un token para evitar ataques por medio CSRF.
     * 
     * @return string
     */
    public function get_token(): string;

    /**
     * Devuelve un hash aleatorio.
     *
     * @return string
     */
    public function get_hash(): string;

    /**
     * Autentica al usuario en caso de que los datos sean correctos.
     *
     * @param DLUser $user Modelo relacionado a la tabla de usuarios del sistema.
     * @param array|DLAuthOptions $options Opcional. Opciones de autenticación.
     * @param DLCookie|null $cookie Opcional. Establece los parámetros de configuración y envío de la cookie.
     * @return bool Retorna `true` si la autenticación fue exitosa, `false` en caso contrario.
     */
    public function auth(DLUser $user, array|DLAuthOptions $options = [], ?DLCookie $cookie): bool;


    /**
     * Permite ejecutar acciones cuadno el usuario está autenticado
     *
     * @return void
     */
    public function logged(callable $callback): void;

    /**
     * Permite ejecutar acciones cuando el usuario no se encuentra autenticado
     *
     * @param callable $callback
     * @return void
     */
    public function not_logged(callable $callback): void;
}
