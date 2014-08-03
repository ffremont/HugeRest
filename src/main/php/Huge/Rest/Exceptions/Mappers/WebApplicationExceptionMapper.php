<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;
use Huge\Rest\Exceptions\WebApplicationException;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;


/**
 * @Component
 */
class WebApplicationExceptionMapper implements IExceptionMapper{

    /**
     * @Autowired("Huge\IoC\Factory\ILogFactory")
     * @var \Huge\IoC\Factory\ILogFactory
     */
    private $loggerFactory;
    
    public function map(\Exception $e) {
        $this->loggerFactory->getLogger(__CLASS__)->error($e);
        
        if($e instanceof WebApplicationException){
            return HttpResponse::status($e->getStatus());
        }else{
            return HttpResponse::status(500);
        }
    }

    public function getLoggerFactory() {
        return $this->loggerFactory;
    }

    public function setLoggerFactory(\Huge\IoC\Factory\ILogFactory $loggerFactory) {
        $this->loggerFactory = $loggerFactory;
    }
}

