<?php

namespace Huge\Rest\Http;

use Huge\Rest\Utils\ConfigInitHelper;

class ConfigInitHelperTest extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @test
     */
    public function valideOk(){
        $items = array(
            '2K' => 2048,
            '8M' => 8388608,
            '1G' => 1073741824
        );
        $it = 0;
        foreach($items as $value => $expected){
            ++$it;
            $this->assertEquals($expected, ConfigInitHelper::convertUnit($value));
        }
        
        $this->assertEquals(count($items), $it);
    }

}


