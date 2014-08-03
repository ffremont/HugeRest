<?php

namespace Huge\Rest\Http;

class HttpFiles {
    
    private $files;

    public function __construct($files) {
        $this->files = $files;
    }
    
    public function getFiles() {
        return $this->files;
    }

    public function setFiles($files) {
        $this->files = $files;
    }

}

