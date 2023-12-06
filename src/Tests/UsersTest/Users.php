<?php
namespace DLTools\Tests\UsersTest;

use DLTools\Auth\DLAuth;
use DLTools\Auth\DLUser;

class Users extends DLUser {
    // protected static ?string $table = "ciencia";

    public function capture_credentials(): bool {

        /**
         * Autenticación del usuario
         * 
         * @var DLAuth
         */
        $auth = DLAuth::get_instance();

        $this->set_username(
            $this->get_required('username')
        );

        $this->set_password(
            $this->get_required('password')
        );
        
        return $auth->auth($this, [
            "username_field" => 'username',
            "password_field" => 'password',
            "token_field" => 'token'
        ]);
    }
}