<?php

namespace DLTools\Auth;

use DLTools\Database\Model;

/**
 * Procesa el usuario.
 * 
 * @package DLTools\Auth
 * 
 * @version 1.0.0 (release)
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @copyright 2023 David E Luna M
 * @license MIT
 */
abstract class DLUser extends Model {

    /**
     * Token de usuario
     *
     * @var string|null
     */
    private ?string $token_user = null;

    /**
     * Usuario de la aplicación
     *
     * @var string|null
     */
    private ?string $username = null;

    /**
     * Contraseña de la aplicación.
     *
     * @var string|null
     */
    private ?string $password_hash = null;

    /**
     * Devuelve el token del usuario
     *
     * @return string|null
     */
    public function get_token(): ?string {
        return $this->token_user;
    }

    /**
     * Establece el token de autenticación del usuario
     *
     * @param string $token
     * @return void
     */
    public function set_token_user(string $token): void {
        $this->token_user = $token;
    }

    /**
     * Establece el nombre de usuario de la aplicación.
     *
     * @param string $username Nombre de usuario
     * @return void
     */
    protected function set_username(string $username): void {
        $this->username = trim($username);
    }

    /**
     * Establece la contraseña enviada por el usuario
     *
     * @param string $password
     * @return void
     */
    protected function set_password(string $password): void {
        $this->password = trim($password);
    }

    /**
     * Establece el hash de la contraseña almacenada en la base de datos
     *
     * @param string $password_hash
     * @return void
     */
    public function set_password_hash(string $password_hash): void {
        $this->password_hash = $password_hash;
    }

    /**
     * Devuelve el usuario de la aplicación
     *
     * @return string|null
     */
    public function get_username(): ?string {
        return $this->username;
    }

    /**
     * Devuelve el hash de la contraseña
     *
     * @return string|null
     */
    public function get_password_hash(): ?string {
        return $this->password_hash;
    }

    /**
     * Devuelve la contraseña enviada por el usuario
     *
     * @return string|null
     */
    public function get_password(): ?string {
        return $this->password;
    }
}
