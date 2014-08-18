<?php

namespace MyWebApi\Resources\Filters;

use Huge\IoC\Annotations\Component;
use Huge\Rest\Process\IRequestFilter;
use Huge\Rest\Exceptions\WebApplicationException;

/**
 * @Component
 */
class AuthFilter implements IRequestFilter{

    const AUTHORIZATION = 'authorization';
    
    /**
     * 
     * @param \Huge\Rest\Http\HttpRequest $request
     * @throws WebApplicationException
     */
    public function doFilter(\Huge\Rest\Http\HttpRequest $request) {
        $auth = $request->getHeader(self::AUTHORIZATION);
        if($auth !== 'TOTO'){
            throw new WebApplicationException('Auth invalide : '.$auth, 401);
        }
    }

}

