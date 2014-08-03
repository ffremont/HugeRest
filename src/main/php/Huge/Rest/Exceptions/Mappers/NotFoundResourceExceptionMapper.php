<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;


/**
 * @Component
 */
class NotFoundResourceExceptionMapper implements IExceptionMapper{

    /**
     * @Autowired("Huge\IoC\Factory\ILogFactory")
     * @var \Huge\IoC\Factory\ILogFactory
     */
    private $loggerFactory;
    
    public function map(\Exception $e) {
        $this->loggerFactory->getLogger(__CLASS__)->error($e);
        
        return HttpResponse::status(404)->entity($e->getMessage());
    }

    public function getLoggerFactory() {
        return $this->loggerFactory;
    }

    public function setLoggerFactory(\Huge\IoC\Factory\ILogFactory $loggerFactory) {
        $this->loggerFactory = $loggerFactory;
    }
}

