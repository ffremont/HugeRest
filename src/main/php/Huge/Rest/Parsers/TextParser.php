<?php

namespace Huge\Rest\Parser;

use Huge\Rest\Process\IRequestParser;

class TextParser implements IRequestParser{

    public function __construct() {
        
    }

    public static function parse(\Huge\Rest\Http\HttpRequest $request) {
        return $request->getBody();
    }

}

