<?php

namespace Huge\Rest\Interceptors;

use Huge\IoC\Annotations\Component;
use Huge\Rest\Process\IInterceptor;

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
     *
     * @var \Logger
     */
    private $logger;
    
    public function __construct() {
        $this->logger = \Logger::getLogger(__CLASS__);
    }

    public function end(\Huge\Rest\Http\HttpResponse $response) {
        $time = ($time_end - $time_start) * 1000; // ms
        $memoryPeak= memory_get_peak_usage() / 1048576;
        $memoryPeak= memory_get_peak_usage() / 1048576;
        
        $this->logger->info('Temps d\'exécution de la requête pendant '.round($time, 2).' ms');
        $this->logger->info('Consommation de '.$this->memoryStart.' mo, avec un pic à '.$memoryPeak.' mo');
    }

    public function start(\Huge\Rest\Http\HttpRequest $request) {
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage() / 1048576;
    }

}

