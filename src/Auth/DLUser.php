<?php

namespace DLTools\Auth;

/**
 * Permite establecer los campos del formulario. Por defecto ya 
 * tiene unos campos prestablecidos cuando no se hayan definido.
 * 
 * @package DLTools
 * 
 * @author David E Luna M <davidlunamontilla@gmail.com>
 * @license MIT
 * @version v1.0.0
 */
class DLUser {
    /**
     * Nombre del campo de usuario
     *
     * @var string
     */
    private string $usernameField = "";

    /**
     * Nombre del campo de contraseña.
     *
     * @var string
     */
    private string $passwordField = "";

    /**
     * Nombre de la tabla de usuarios
     *
     * @var string
     */
    private string $usersTable = "";

    /**
     * Nombre del campo de correo electrónico.
     *
     * @var string
     */
    private string $emailNameField = "";

    /**
     * Nombre de la tabla donde se almacenan los token de sessión
     *
     * @var string
     */
    private string $sessionTable = "";

    /**
     * Nombre del campo donde se almacena el token de la sesión.
     *
     * @var string
     */
    private string $tokenName = "";

    /**
     * Nombre del campo ID de la tabla de usuarios.
     *
     * @var string
     */
    private string $IdName = "";

    /**
     * Nombre del campo oculto del formulario que terminará
     * la sesión del usuario.
     *
     * @var string
     */
    private string $logoutField = "";

    /**
     * Array par `clave => valor` para permitir cambiar el nombre de las
     * tablas de usuario y sessión en el caso de ser necesario. En el caso de que no se
     * definan los valores por defectos serán:
     * 
     * ```
     * $user = [
     *  'usernameField' => 'username',
     *  'passwordField' => 'password',
     *  'usersTable' => 'users',
     *  'emailNameField' => 'email',
     *  'sessionTable' => 'session',
     *  'tokenName' => 'token'
     * ]
     * ```
     * 
     * Donde el valor es el nombre del campo o la tabla. No importa en qué orden
     * lo coloque. La clase `DLUser` solamente tomará los valores que haya definido.
     *
     * @param array $user
     */
    public function __construct(array $user = []) {
        $options = (object) $user;

        $this->usernameField = $options->usernameField ?? 'username';
        $this->passwordField = $options->passwordField ?? 'password';
        $this->usersTable = $options->usersTable ?? 'users';
        $this->emailNameField = $options->emailNameField ?? 'email';
        $this->sessionTable = $options->sessionTable ?? 'session';
        $this->tokenName = $options->tokenName ?? 'token';
        $this->IdName = $options->IdName ?? 'ID';
        $this->logoutField = $options->logoutField ?? 'logout';
    }

    /**
     * Devuelve en un objeto los nombres de los campos de la tabla de usuario. 
     * En este caso, los campos obligatorio y/o principales.
     *
     * @return object
     */
    public function get_credentials_fields(): object {
        return (object) [
            "IdName" => $this->IdName,
            "usernameField" => $this->usernameField,
            "passwordField" => $this->passwordField,
            "emailNameField" => $this->emailNameField,
            "usersTable" => $this->usersTable,
            "sessionTable" => $this->sessionTable,
            "tokenName" => $this->tokenName,
            "logoutField" => $this->logoutField
        ];
    }
}
