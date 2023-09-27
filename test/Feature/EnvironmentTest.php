<?php

use DLTools\Config\DLEnvironment;
use PHPUnit\Framework\TestCase;
class EnvironmentTest extends TestCase {

    use DLEnvironment;

    /**
     * Ejecuta una prueba de funcionamiento.
     * 
     * @test
     *
     * @return void
     */
    public function run_test(): void {
        /**
         * Valor esperado.
         * 
         * @var string
         */
        $expected = "Algo";

        /**
         * Valor actual
         * 
         * @var string
         */
        $actual = "Algo";

        $this->assertEquals($expected, $actual);
    }
}
