<?php

namespace Huge\Rest\Process;

use Huge\Rest\Http\HttpRequest;

interface IRequestFilter {
    public function doFilter(HttpRequest $request);
}

