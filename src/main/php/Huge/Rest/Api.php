<?php

namespace Huge\Rest;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;
use Doctrine\Common\Cache\Cache;
use Huge\Rest\Http\HttpRequest;
use Huge\Rest\Routing\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Huge\IoC\Utils\IocArray;

/**
 * @Component
 */
class Api {

    /**
     * Implémentation de cache pour la mise en cache de l'API REST
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
     * @Autowired("Huge\Rest\Routing\Route")
     * @var \Huge\Rest\Routing\Route
     */
    private $route;

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
     * ContextRoot de l'application : 
     * Exemple : /services/personn/1 => services
     * 
     * @var string 
     */
    private $contextRoot;

    /**
     *
     * @var \Logger
     */
    private $logger;

    private static $TOKENS = array(
        ':string' => '([a-zA-Z]+)',
        ':number' => '([0-9]+)',
        ':alpha' => '([a-zA-Z0-9-_]+)'
    );

    public function __construct($contextRoot = '', $cache = null) {
        $this->contextRoot = trim($contextRoot, '/');
        $this->cacheImpl = $cache;
        $this->routes = array();
        $this->route = null;
        $this->logger = \Logger::getLogger(__CLASS__);
    }

    public function loadRoutes() {
        $cacheKey = __CLASS__ . $this->webAppIoC->getVersion() . '_loadRoutes';
        if ($this->cacheImpl !== null) {
            $routes = $this->cacheImpl->fetch($cacheKey);
            if ($routes !== FALSE) {
                $this->routes = $routes;
                return;
            }
        }

        $annotationReader = new AnnotationReader();
        $resources = $this->webAppIoC->getResources();
        $definitions = $this->webAppIoC->getDefinitions();
        foreach ($resources as $idBean) {
            $definition = $definitions[$idBean];
            $classPrefix = '';
            $oRClass = new \ReflectionClass($definition['class']);
            $oPath = $annotationReader->getClassAnnotation($oRClass, 'Huge\Rest\Annotations\Path');
            if ($oPath !== null) {
                $classPrefix = trim($oPath->value, '/');
            }
            $methods = $oRClass->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $oRMethod) {
                $path = $classPrefix;
                $oPathMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Path');
                $oConsumes = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Consumes');

                $oGetMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Get');
                $oPutMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Put');
                $oPostMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Post');
                $oDeleteMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Delete');
                if (($oGetMethod === null) && ($oPutMethod === null) && ($oPostMethod === null) && ($oDeleteMethod === null)) {
                    continue;
                }
                $meths = array();
                if ($oGetMethod !== null)
                    $meths[] = 'GET';
                if ($oPutMethod !== null)
                    $meths[] = 'PUT';
                if ($oPostMethod !== null)
                    $meths[] = 'POST';
                if ($oDeleteMethod !== null)
                    $meths[] = 'DELETE';

                if ($oPathMethod !== null) {
                    $path = $path . '/' . trim($oPathMethod->value, '/');
                }

                $route = array(
                    'idBean' => $idBean,
                    'classResource' => $definition['class'],
                    'methodResource' => $oRMethod->getName(),
                    'uri' => $path,
                    'methods' => $meths,
                    'contentTypes' => $oConsumes === null ? null : $oConsumes->value
                );
                $this->routes[md5(serialize($route))] = $route;
            }
        }

        if ($this->cacheImpl !== null) {
            $this->cacheImpl->save($cacheKey, $this->routes);
        }
    }

    /**
     * Retourne une route à partir de la requête HTTP
     * 
     * @return \Huge\Rest\Routing\Route
     */
    public function findRoute(Http\HttpRequest $request) {
        foreach ($this->routes as $route) {
            if (!empty($route['methods']) && !IocArray::in_array($request->getMethod(), $route['methods'])) {
                continue;
            }
            if (!empty($route['contentTypes']) && !IocArray::in_array($request->getContentType(), $route['contentTypes'])) {
                continue;
            }
            
            $matches = array();
            if (preg_match('#^' . ($this->contextRoot === '' ? '' : $this->contextRoot.'/').strtr($route['uri'], self::$TOKENS) . '$#', $request->getUri(), $matches)) {
                array_shift($matches);
                $this->route->init(array(
                    'resourceClass' => $route['classResource'],
                    'methodClass' => $route['methodResource'],
                    'idBean' => $route['idBean'],
                    'uri' => $request->getUri(),
                    'contentType' => $request->getContentType(),
                    'method' => $request->getMethod(),
                    'matches' => $matches
                ));
                break;
            }

            if ($this->route->isInit()) {
                break;
            }
        }
    }

    public function run() {
        $this->loadRoutes();
        $this->findRoute($this->request);
        $httpResponse = null;
        
        try{
            $beansFilter = $this->webAppIoC->findBeansByImpl('Huge\Rest\Process\IFilter');
            $filtersMapping = $this->webAppIoC->getFiltersMapping();
            foreach($beansFilter as $idBeanFilter){
                if(isset($filtersMapping[$idBeanFilter])){
                    foreach($filtersMapping[$idBeanFilter] as $pathRegExp){
                        if(preg_match('#'.$pathRegExp.'#', $this->request->getUri())){
                            $this->webAppIoC->getBean($idBeanFilter)->doFilter($this->request);
                            break;
                        }
                    }
                }else{
                    $this->webAppIoC->getBean($idBeanFilter)->doFilter($this->request);
                }
            }
            $beansInterceptor = $this->webAppIoC->findBeansByImpl('Huge\Rest\Process\IInterceptor');
            foreach($beansInterceptor as $idBeanInterceptor){
                $this->webAppIoC->getBean($idBeanInterceptor)->start($this->request);
            }
            
            $httpResponse = call_user_func_array(array($this->webAppIoC->getBean($this->route->getIdBean()), $this->route->getMethodClass()), $this->route->getMatches());
            
            foreach($beansInterceptor as $idBeanInterceptor){
                $this->webAppIoC->getBean($idBeanInterceptor)->end($httpResponse);
            }
        }catch(\Exception $e){
            $beans = $this->webAppIoC->findBeansByImpl('Huge\Rest\Process\IExceptionMapper');
            if(count($beans) === 1){
                $httpResponse = $this->webAppIoC->getBean($beans[0])->map($e);
            }
        }
        
        if(($httpResponse !== null) && ($httpResponse instanceof \Huge\Rest\Http\HttpResponse)){
            $httpResponse->build();
        }
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

    public function getContextRoot() {
        return $this->contextRoot;
    }

    public function setContextRoot($contextRoot) {
        $this->contextRoot = trim($contextRoot, '/');
    }
    
    public function getRoute() {
        return $this->route;
    }

    public function setRoute(\Huge\Rest\Routing\Route $route) {
        $this->route = $route;
    }
}

