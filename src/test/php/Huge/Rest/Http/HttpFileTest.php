<?php

namespace Huge\Rest\Http;

use Huge\Rest\Http\HttpFile;

class HttpFileTest extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @test
     */
    public function createFileOk() {
        $temp_file = tempnam(sys_get_temp_dir(), 'Tux');
        file_put_contents($temp_file, 'Hello world');
        
        $_FILES = array(
            'file_valid' => array(
                'name' => 'Tux.txt',
                'tmp_name' => $temp_file,
                'type' => 'text/plain',
                'size' => filesize($temp_file),
                'error' => 0
            )
        );
        
        $ex = false;
        $file = null;
        try{
            $file = new HttpFile('file_valid');
        }catch(\Exception $e){
            $ex = true;
        }
        
        $this->assertFalse($ex);
        $this->assertTrue($file->isUploaded());
        $this->assertEquals('Hello world', $file->getContents());
    }
    
     /**
     * @test
     */
    public function createFileOverSizeOk() {
        $temp_file = tempnam(sys_get_temp_dir(), 'Tux');
        file_put_contents($temp_file, 'Hello world');
        
        $_FILES = array(
            'file_valid' => array(
                'name' => 'Tux.txt',
                'tmp_name' => $temp_file,
                'type' => 'text/plain',
                'size' => filesize($temp_file),
                'error' => 0
            )
        );
        
        $ex = null;
        try{
            $file = new HttpFile('file_valid', filesize($temp_file)-1);
        }catch(\Exception $e){
            $ex = $e;
        }
        
        $this->assertNotNull($ex);
        $this->assertTrue($ex instanceof \Huge\Rest\Exceptions\UploadException);
    }

}

