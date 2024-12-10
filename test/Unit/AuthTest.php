<?php
session_start();

use DLTools\Auth\DLAuth;
use PHPUnit\Framework\TestCase;


class AuthTest extends Testcase {
    private DLAuth $auth;

    public function setup(): void {
        $this->auth = DLAuth::get_instance();
    }

    public function test_auth(): void {
        $string = $this->auth->get_token();
        $this->assertNotEmpty($string, 'La cadena se encuentra vacía');
    }

    public function test_true(): void {
        $this->assertTrue(true);
    }
}
