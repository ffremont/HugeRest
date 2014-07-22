<?php

namespace Huge\Rest\Process;

use Huge\Rest\Http\HttpRequest;
use Huge\Rest\Http\HttpResponse;

interface IInterceptor {
    public function start(HttpRequest $request);
    public function end(HttpResponse $response);
}

