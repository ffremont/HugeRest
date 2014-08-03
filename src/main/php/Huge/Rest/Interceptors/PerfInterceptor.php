<?php

namespace Huge\Rest\Interceptors;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;
use Huge\Rest\Process\IInterceptor;
use Huge\IoC\Factory\ILogFactory;

use Huge\Rest\Http\HttpRequest;

/**
 * @Component
 */
class PerfInterceptor implements IInterceptor{

    /**
     * Date de début de la capture
     * 
     * @var int
     */
    private $startTime;
    
    /**
     * Taille en Mo occupé par le script à la phase start (avant les traitements applicatifs) 
     * 
     * @var int
     */
    private $memoryStart;
    
    /**
     * @Autowired("Huge\Rest\Http\HttpRequest")
     * @var \Huge\Rest\Http\HttpRequest 
     */
    private $request;
    
    /**
     * @Autowired("Huge\IoC\Factory\ILogFactory")
     * @var \Huge\IoC\Factory\ILogFactory
     */
    private $loggerFactory;
    
    public function __construct() {}

    public function end(\Huge\Rest\Http\HttpResponse $response) {
        $logger = $this->loggerFactory->getLogger(__CLASS__);
                
        $time = (microtime(true) - $this->startTime) * 1000; // ms
        $memoryPeak= memory_get_peak_usage() / 1048576;
        $memoryPeak= memory_get_peak_usage() / 1048576;
        
        if($this->request !== null){
            $logger->info('Performance de la ressource : '.$this->request->getUri());
        }
        
        $logger->info('Temps d\'exécution de la requête pendant '.round($time, 2).' ms');
        $logger->info('Consommation de '.$this->memoryStart.' mo, avec un pic à '.round($memoryPeak,2).' mo');
    }

    public function start(\Huge\Rest\Http\HttpRequest $request) {
        $this->startTime = microtime(true);
        $this->memoryStart = round(memory_get_usage() / 1048576, 2);
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest(\Huge\Rest\Http\HttpRequest $request) {
        $this->request = $request;
    }

    public function getLoggerFactory() {
        return $this->loggerFactory;
    }

    public function setLoggerFactory(\Huge\IoC\Factory\ILogFactory $loggerFactory) {
        $this->loggerFactory = $loggerFactory;
    }
}

