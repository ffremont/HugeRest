<?php

namespace Huge\Rest;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;
use Doctrine\Common\Cache\Cache;
use Huge\Rest\Http\HttpRequest;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @Component
 */
class Api {

    /**
     *
     * @var \Doctrine\Common\Cache\Cache
     */
    private $cacheImpl;
    
    /**
     *
     * @Autowired("Huge\Rest\WebAppIoC")
     * @var \Huge\Rest\WebAppIoC
     */
    private $webAppIoC;

    /**
     *
     * @Autowired("Huge\Rest\Http\HttpRequest")
     * @var \Huge\Rest\Http\HttpRequest
     */
    private $request;
    
    /**
     *
     * @var array
     *      array
     *          idBean => 
     *          uri => personne/:string
     *          method => POST
     *          contentType => application/json
     */
    private $routes;
    
    /**
     *
     * @var \Logger
     */
    private $logger;

    public function __construct($cache = null) {
        $this->cacheImpl = $cache;
        $this->routes = array();
        $this->logger = \Logger::getLogger(__CLASS__);
    }

    public function loadRoutes() {
        $cacheKey = __CLASS__.$this->webAppIoC->getVersion().'_loadRoutes';
        if (!is_null($this->cacheImpl)) {
            $routes = $this->cacheImpl->fetch($cacheKey);
            if ($routes !== FALSE) {
                $this->routes = $routes;
                return;
            }
        }
        
        $annotationReader = new AnnotationReader();
        $resources = $this->webAppIoC->getResources();
        $definitions = $this->webAppIoC->getDefinitions();
        foreach($resources as $idBean){
            $definition = $definitions[$idBean];
            $classPrefix = '';
            $oRClass = new \ReflectionClass($definition['class']);
            $oPath = $annotationReader->getClassAnnotation($oRClass, 'Huge\Rest\Annotations\Path');
            if(!is_null($oPath)){
                $classPrefix = trim($oPath->value, '/');
            }
            $methods = $oRClass->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);
            foreach($methods as $oRMethod){
                $path = $classPrefix;
                $oPathMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Path');
                $oConsumes = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Consumes');
               
                $oGetMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Get');
                $oPutMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Put');
                $oPostMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Post');
                $oDeleteMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Delete');
                if(is_null($oGetMethod) && is_null($oPutMethod) && is_null($oPostMethod) && is_null($oDeleteMethod) ){
                    continue;
                }
                $meths = array();
                if(!is_null($oGetMethod))
                    $meths[] = 'GET';
                if(!is_null($oPutMethod))
                    $meths[] = 'PUT';
                if(!is_null($oPostMethod))
                    $meths[] = 'POST';
                if(!is_null($oDeleteMethod))
                    $meths[] = 'DELETE';
                
                if(!is_null($oPathMethod)){
                    $path = $path.'/'. trim($oPathMethod->value, '/');
                }
                
                $route = array(
                    'idBean' => $idBean,
                    'uri' => $path,
                    'methods' => $meths,
                    'contentTypes' => is_null($oConsumes) ? null : $oConsumes->value
                );
                $this->routes[md5(serialize($route))] = $route;
            }
        }
        
        if (!is_null($this->cacheImpl)) {
            $this->cacheImpl->save($cacheKey, $this->routes);
        }
    }

    public function findRoute(Http\HttpRequest $request) {
        foreach($this->routes as $route){
            if(!empty($route['methods']) && !in_array($request->getMethod(), $route['methods'])){
                continue;
            }
            if(!empty($route['contentTypes']) && !in_array($request->getContentType(), $route['contentTypes'])){
                continue;
            }
            
        }        
    }

    public function run() {
        
    }

    /**
     * 
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCacheImpl() {
        return $this->cacheImpl;
    }

    public function setCacheImpl(Cache $cacheImpl) {
        $this->cacheImpl = $cacheImpl;
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest(HttpRequest $request) {
        $this->request = $request;
    }
    
    public function getWebAppIoC() {
        return $this->webAppIoC;
    }

    public function setWebAppIoC(WebAppIoC $webAppIoC) {
        $this->webAppIoC = $webAppIoC;
    }
    
    public function getRoutes() {
        return $this->routes;
    }

}

