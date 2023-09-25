<?php

use DLTools\HttpRequest\DLRequest;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase {

    private DLRequest $request;

    /**
     * @before
     *
     * @return void
     */
    public function setup(): void {
        $this->request = DLRequest::get_instance();
    }

    public function testRequestPostTrue(): void {
        // Emulando una petición HTTP:
        $_REQUEST = [
            "name" => "David",
            "lastname" => "Luna"
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = [
            "name" => true,
            "lastname" => true
        ];

        $this->assertTrue($this->request->post($request));
    }

    public function testRequestPostOptionalTrue(): void {
        // Emulando una petición HTTP:
        $_REQUEST = [
            "name" => "David",
            "lastname" => "Luna"
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = [
            "name" => true,
            "lastname" => false
        ];

        $this->assertTrue($this->request->post($request));
    }

    public function testRequestGetFalseOptional(): void {
        // Emulando una petición HTTP:
        $_REQUEST = [
            "name" => "David",
            "lastname" => "Luna"
        ];

        $request = [
            "name" => false,
            "username" => true
        ];

        $this->assertFalse($this->request->get($request));
    }

    public function testModulesFalse(): void {
        // Emulando una petición HTTP:
        $_REQUEST = [
            "name" => "David",
            "lastname" => "Luna"
        ];

        $request = ["nameCiencia"];

        $this->assertFalse($this->request->modules($request));
    }

    public function testModulesTrue(): void {
        // Emulando una petición HTTP:
        $_REQUEST = [
            "name" => "David",
            "lastname" => "Luna"
        ];

        $request = ["name"];

        $this->assertTrue($this->request->modules($request));
    }

    public function testModulesTrue2(): void {
        // Emulando una petición HTTP:
        $_REQUEST = [
            "name" => "",
            "lastname" => ""
        ];

        $request = ["lastname"];

        $this->assertTrue($this->request->modules($request));
    }
}