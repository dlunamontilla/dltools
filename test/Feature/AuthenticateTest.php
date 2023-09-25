<?php

use PHPUnit\Framework\TestCase;
use DLTools\Models\Authenticate;

class EnvironmentTest extends TestCase {

   /**
    * @test
    * 
    * @return void
    */

   public function trueValue(): void {
      $this->assertTrue(true);
   }
}
