<?php

namespace Huge\Rest\Utils4Test;

use Huge\IoC\Annotations\Component;

use Huge\IoC\Factory\ILogFactory;

/**
 * @Component
 */
class Log4phpFactory implements ILogFactory{

    public function __construct() {}
    
    /**
     * 
     * @param string $name
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger($name){
        return new Log4phpLogger($name);
    }

}

