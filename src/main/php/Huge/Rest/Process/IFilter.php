<?php

namespace Huge\Rest\Process;

use Huge\Rest\Http\HttpRequest;

interface IFilter {
    public function doFilter(HttpRequest $request);
}

