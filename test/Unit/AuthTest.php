<?php
session_start();

use DLTools\Auth\DLAuth;
use PHPUnit\Framework\TestCase;


class AuthTest extends Testcase {
    /**
     * Obtjeto de autenticación
     *
     * @var DLAuth
     */
    private DLAuth $auth;

    /**
     * @before
     *
     * @return void
     */
    public function setup(): void {
        $this->auth = DLAuth::get_instance();
    }

    public function testAuth(): void {
        $string = $this->auth->get_token();
        $this->assertNotEmpty($string, 'La cadena se encuentra vacía');
    }
}