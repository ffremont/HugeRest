<?php

namespace Huge\Rest\Process;

use Huge\Rest\Http\HttpRequest;

interface IRequestParser {
    public static function parse(HttpRequest $request);
}

?>
