<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;

use Huge\IoC\Annotations\Component;


/**
 * @Component
 */
class DefaultExceptionMapper implements IExceptionMapper{

    /**)
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
    public function __construct(\Huge\IoC\Factory\ILogFactory $factory){
        $this->logger = $factory->getLogger(__CLASS__);
    }
    
    public function map(\Exception $e) {
        $this->logger->error($e);
        
        return HttpResponse::status(500);
    }
    
}

