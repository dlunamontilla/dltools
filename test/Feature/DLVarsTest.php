<?php

use DLTools\Config\Credentials;
use DLTools\Config\DLEnvironment;
use PHPUnit\Framework\TestCase;

class DLVarsTest extends TestCase {

    use DLEnvironment;

    private ?Credentials $credentials = null;

    public function setup(): void {
        $this->credentials = $this->get_credentials();
    }

    public function test_production(): void {
        $value = $this->credentials->is_production();
        $this->assertIsBool($value);
    }

    public function test_database_host(): void {
        $value = $this->credentials->get_host();
        $this->assertIsString($value);
    }

    public function test_database_port(): void {
        $value = $this->credentials->get_port();
        $this->assertIsInt($value);
    }

    public function test_mail_port(): void {
        $value = $this->credentials->get_mail_port();
        $this->assertIsInt($value);
    }
}
