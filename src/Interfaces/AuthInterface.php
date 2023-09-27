<?php

namespace DLTools\Interfaces;

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
     * Devuelve un identificador único universal para un usuario autenticado
     *
     * @return string
     */
    public function get_user_uuid(): string;

    /**
     * Devuelve un identificador numérico del usuario.
     * 
     * @return integer
     */
    public function get_user_id(): int;

    /**
     * Devuelve el nombre de usuario autenticado.
     *
     * @return string
     */
    public function get_username(): string;

    /**
     * Devuelve los dos nombres del usuario
     *
     * @return string
     */
    public function get_name(): string;

    /**
     * Devuelve los apellidos del usuario
     *
     * @return string
     */
    public function get_lastname(): string;

    /**
     * Devuelve un hash aleatorio.
     *
     * @return string
     */
    public function get_hash(): string;

    /**
     * Autentica el usuario, en el caso de los datos sean correctos.
     *
     * @return void
     */
    public function auth(DLUser $user): void;

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