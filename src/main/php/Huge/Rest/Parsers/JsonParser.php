<?php

namespace Huge\Rest\Parser;

use Huge\Rest\Process\IRequestParser;

class JsonParser implements IRequestParser{

    public function __construct() {
        
    }

    public static function parse(\Huge\Rest\Http\HttpRequest $request) {
        return json_decode($request->getBody());
    }

}

