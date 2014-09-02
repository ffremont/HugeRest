<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;

use Huge\IoC\Annotations\Component;


/**
 * @Component
 */
class NotFoundResourceExceptionMapper implements IExceptionMapper{

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
        
        return HttpResponse::status(404)->entity($e->getMessage());
    }
}

