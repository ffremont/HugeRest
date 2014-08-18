<?php

namespace Huge\Rest\Process;

use Huge\Rest\Http\HttpResponse;

interface IResponseFilter {
    public function doFilter(HttpResponse $response);
}

