<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;
use Huge\Rest\Exceptions\WebApplicationException;

use Huge\IoC\Annotations\Component;


/**
 * @Component
 */
class WebApplicationExceptionMapper implements IExceptionMapper{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
     /**
     * 
     * @param \Huge\IoC\Factory\ILogFactory $factory
     */
    public function __construct($factory){
        $this->logger = $factory === null ? $factory : $factory->getLogger(__CLASS__);
    }
    
    public function map(\Exception $e) {
        if($this->logger !== null){
            $this->logger->error($e);
        }
        
        if($e instanceof WebApplicationException){
            return HttpResponse::status($e->getStatus());
        }else{
            return HttpResponse::status(500);
        }
    }
}

