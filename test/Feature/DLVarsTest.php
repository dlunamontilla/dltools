<?php

use DLTools\Config\Credentials;
use DLTools\Config\DLEnvironment;
use PHPUnit\Framework\TestCase;

class DLVarsTest extends TestCase {

    use DLEnvironment;
    private ?Credentials $dlvars = null;

    /**
     * @before
     *
     * @return void
     */
    public function setup(): void {
        $this->parse_file();

        $vars = $this->get_environments_as_object();

        $this->dlvars = new Credentials($vars);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_production(): void {
        $value = $this->dlvars->is_production();
        $this->assertIsBool($value);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_database_host(): void {
        $value = $this->dlvars->get_host();
        $this->assertIsString($value);
    }

    /**
     * @test
     *
     * @return void
     */
    public function test_databse_port(): void {
        $value = $this->dlvars->get_port();
        $this->assertIsInt($value);
    }
}