<?php

namespace Huge\Rest\Data;

class TempFile {

    private $path;
    
    public function __construct($path) {
        $this->path = $path;
    }
    
    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

}

