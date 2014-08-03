<?php

namespace Huge\Rest\Log;

use Huge\IoC\Annotations\Component;

/**
 * @Component
 */
class NullLoggerFactory implements \Huge\IoC\Factory\ILogFactory{

    public function __construct() {}

    /**
     * 
     * @param string $name
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger($name) {
        return new \Psr\Log\NullLogger();
    }

}

