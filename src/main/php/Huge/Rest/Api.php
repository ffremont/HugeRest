<?php

namespace Huge\Rest;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;
use Doctrine\Common\Cache\Cache;
use Huge\Rest\Http\HttpRequest;
use Huge\Rest\Routing\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Huge\IoC\Utils\IocArray;
use Huge\Rest\Exceptions\NotFoundException;

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

            $oConsumesClass = $annotationReader->getClassAnnotation($oRClass, 'Huge\Rest\Annotations\Consumes');
            $oProducesClass = $annotationReader->getClassAnnotation($oRClass, 'Huge\Rest\Annotations\Produces');
            $methods = $oRClass->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $oRMethod) {
                $path = $classPrefix;
                $oPathMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Path');
                $oConsumesMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Consumes');
                $oProducesMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Produces');

                $oGetMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Get');
                $oPutMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Put');
                $oPostMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Post');
                $oDeleteMethod = $annotationReader->getMethodAnnotation($oRMethod, 'Huge\Rest\Annotations\Delete');
                if (($oGetMethod === null) && ($oPutMethod === null) && ($oPostMethod === null) && ($oDeleteMethod === null)) {
                    continue;
                }
                $meths = array();
                if ($oGetMethod !== null) {
                    $meths[] = 'GET';
                    $meths[] = 'HEAD';
                }
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
                    'consumes' => $oConsumesMethod === null ? ($oConsumesClass === null ? null : $oConsumesClass->value) : $oConsumesMethod->value,
                    'produces' => $oProducesMethod === null ? ($oProducesClass === null ? null : $oProducesClass->value) : $oProducesMethod->value
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
    public function processRoute(Http\HttpRequest $request) {
        foreach ($this->routes as $route) {
            if (!empty($route['methods']) && !IocArray::in_array($request->getMethod(), $route['methods'])) {
                continue;
            }

            if ( is_array($route['consumes']) &&  is_array($request->getAccepts())) {
                $aIntersectAccept = array_intersect($route['consumes'], $request->getAccepts());
                if (!empty($route['consumes']) && empty($aIntersectAccept)) {
                    continue;
                }
            }

            $matches = array();
            if (preg_match('#^' . ($this->contextRoot === '' ? '' : $this->contextRoot . '/') . strtr($route['uri'], self::$TOKENS) . '$#', $request->getUri(), $matches)) {
                array_shift($matches);
                $this->route->init(array(
                    'resourceClass' => $route['classResource'],
                    'methodClass' => $route['methodResource'],
                    'idBean' => $route['idBean'],
                    'uri' => $request->getUri(),
                    'contentType' => $request->getContentType(),
                    'produces' => $route['produces'],
                    'method' => $request->getMethod(),
                    'matches' => $matches
                ));
                break;
            }

            if ($this->route->isInit()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne le nom de la classe qui implémente IBodyWriter afin de convertir le contenu de la réponse
     * La fonction tient compte des préférences "Produces" et des en-têtes "accept"
     * 
     * @return string
     */
    private function _extractClassProduce() {
        $outputMimeType = 'application/json';
        if ($this->route->getProduces() !== null) {
            $produces = $this->route->getProduces();
            $firstProduce = $produces[0];
            $accepts = $this->request->getAccepts();
            if (empty($accepts)) {
                $outputMimeType = $firstProduce;
            } else {
                $intersect = array_intersect($produces, $accepts);
                $outputMimeType = empty($intersect) ? $firstProduce : $intersect[0];
            }
        }

        return $this->webAppIoC->getBodyWriter($outputMimeType);
    }

    /**
     * Démarre l'analyse de la requête et le dispatch 
     */
    public function run() {
        $this->loadRoutes();
        $this->processRoute($this->request);
        /* @var $httpResponse \Huge\Rest\Http\HttpResponse */
        $httpResponse = null;

        try {
            if (!$this->route->isInit()) {
                throw new NotFoundException($this->request->getUri());
            }

            // analyse le contenu pour le parser
            if (IocArray::in_array($this->route->getMethod(), array('POST', 'PUT'))) {
                $bodyReaderClassName = $this->webAppIoC->getBodyReader($this->request->getContentType());
                if (IocArray::in_array('Huge\Rest\Process\IBodyReader', class_implements($bodyReaderClassName))) {
                    $this->request->setEntity(call_user_func_array($bodyReaderClassName . '::read', array($this->request->getBody())));
                } else {
                    $this->logger->warn('Parsing de la requête HTTP impossible car la classe "' . $bodyReaderClassName . '" n\'implémente pas Huge\Rest\Process\IBodyReader');
                }
            }

            $beansFilter = $this->webAppIoC->findBeansByImpl('Huge\Rest\Process\IFilter');
            $filtersMapping = $this->webAppIoC->getFiltersMapping();
            foreach ($beansFilter as $idBeanFilter) {
                if (isset($filtersMapping[$idBeanFilter])) {
                    if (preg_match('#' . $filtersMapping[$idBeanFilter] . '#', $this->request->getUri())) {
                        $this->webAppIoC->getBean($idBeanFilter)->doFilter($this->request);
                        break;
                    }
                } else {
                    $this->webAppIoC->getBean($idBeanFilter)->doFilter($this->request);
                }
            }
            $beansInterceptor = $this->webAppIoC->findBeansByImpl('Huge\Rest\Process\IInterceptor');
            foreach ($beansInterceptor as $idBeanInterceptor) {
                $this->webAppIoC->getBean($idBeanInterceptor)->start($this->request);
            }

            $httpResponse = call_user_func_array(array($this->webAppIoC->getBean($this->route->getIdBean()), $this->route->getMethodClass()), $this->route->getMatches());

            foreach ($beansInterceptor as $idBeanInterceptor) {
                $this->webAppIoC->getBean($idBeanInterceptor)->end($httpResponse);
            }

            // Write entity
            if ($httpResponse->hasEntity()) {
                $bodyWriterClassName = $this->_extractClassProduce();
                if (IocArray::in_array('Huge\Rest\Process\IBodyWriter', class_implements($bodyWriterClassName))) {
                    $httpResponse->body(call_user_func_array($bodyWriterClassName . '::write', array($httpResponse->getEntity())));
                } else {
                    $this->logger->warn('Writing de la réponse HTTP impossible car la classe "' . $bodyWriterClassName . '" n\'implémente pas Huge\Rest\Process\IBodyWriter');
                }
            }
        } catch (\Exception $e) {
            $exceptionMapperClassName = $this->webAppIoC->getExceptionMapper(get_class($e));
            $exceptionMapperClassName = $exceptionMapperClassName === null ? $this->webAppIoC->getExceptionMapper('Exception') : $exceptionMapperClassName;

            $impls = $exceptionMapperClassName !== null ? class_implements($exceptionMapperClassName) : array();
            if (IocArray::in_array('Huge\Rest\Process\IExceptionMapper', $impls)) {
                $httpResponse = call_user_func_array($exceptionMapperClassName . '::map', array($e));
            } else {
                $httpResponse = Http\HttpResponse::code(500);
            }
        }

        if (($httpResponse !== null) && ($httpResponse instanceof \Huge\Rest\Http\HttpResponse)) {
            if ($this->request->getMethod() === 'HEAD') {
                $httpResponse->build(false);
            } else {

                $httpResponse->build();
            }
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

