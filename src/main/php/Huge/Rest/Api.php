<?php

namespace Huge\Rest;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;
use Doctrine\Common\Cache\Cache;
use Huge\Rest\Http\HttpRequest;
use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Routing\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Huge\IoC\Utils\IocArray;
use Huge\IoC\Utils\Caller;
use Huge\Rest\Exceptions\NotFoundResourceException;
use Huge\Rest\Exceptions\BadImplementationException;
use Huge\Rest\Exceptions\InvalidResponseException;
use Huge\Rest\Exceptions\WebApplicationException;

/**
 * @Component
 */
class Api {

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
     * Liste des tokens utilisable dans les path des ressources
     * 
     * @var array
     */
    private static $TOKENS = array(
        ':mString' => '([a-zA-Z]+)',
        ':mNumber' => '([0-9]+)',
        ':mAlpha' => '([a-zA-Z0-9-_\.]+)',
        ':oString' => '([a-zA-Z]*)',
        ':oNumber' => '([0-9]*)',
        ':oAlpha' => '([a-zA-Z0-9-_\.]*)',
        '/' => '\/'
    );

    public function __construct() {
        $this->contextRoot = '';
        $this->routes = array();
    }

    /**
     * Charge les routes disponibles
     */
    public function loadRoutes() {
        $cacheKey = md5($this->webAppIoC->getName() . $this->webAppIoC->getVersion() . __FUNCTION__);
        if ($this->webAppIoC->getApiCacheImpl() !== null) {
            $routes = $this->webAppIoC->getApiCacheImpl()->fetch($cacheKey);
            if ($routes !== FALSE) {
                $this->routes = $routes;
                return;
            }
        }

        $annotationReader = new AnnotationReader();
        $resources = $this->webAppIoC->getResources();
        foreach ($resources as $idBean) {
            $definition = $this->webAppIoC->getDefinitionById($idBean);

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
                    'classResource' => $definition['class'], // nom de la classe ressource
                    'methodResource' => $oRMethod->getName(), // nom de la méthode de la ressource
                    'uri' => $path,
                    'methods' => $meths, // liste des méthodes HTTP
                    'consumes' => $oConsumesMethod === null ? ($oConsumesClass === null ? null : $oConsumesClass->value) : $oConsumesMethod->value,
                    'produces' => $oProducesMethod === null ? ($oProducesClass === null ? null : $oProducesClass->value) : $oProducesMethod->value
                );
                $this->routes[] = $route;
            }
        }

        if ($this->webAppIoC->getApiCacheImpl() !== null) {
            $this->webAppIoC->getApiCacheImpl()->save($cacheKey, $this->routes);
        }
    }

    /**
     * Traite la requête HTTP
     * 
     * @return \Huge\Rest\Routing\Route
     */
    public function processRoute(Http\HttpRequest $request) {
        $negotiator = new \Negotiation\FormatNegotiator();

        $count = count($this->routes);
        for ($i = 0; $i < $count; $i++) {
            $route = $this->routes[$i];
            if (!empty($route['methods']) && !IocArray::in_array($request->getMethod(), $route['methods'])) {
                continue;
            }

            $matches = array();
            $replaceTokens = strtr($route['uri'], self::$TOKENS);
            if (preg_match('#^' . ($this->contextRoot === '' ? '' : $this->contextRoot . '/') . $replaceTokens . '$#', $request->getUri(), $matches) !== 1) {
                continue;
            }

            $produce = HttpResponse::DEFAULT_CONTENT_TYPE;
            if (IocArray::in_array($request->getMethod(), array('POST', 'PUT'))) {
                // consumes match contentType
                if (($route['consumes'] !== null) && ($request->getContentType() !== null)) {
                    $aIntersectContentType = array_intersect($route['consumes'], array($request->getContentType()));
                    if (empty($aIntersectContentType)) {
                        continue;
                    }
                }

                if ($route['produces'] !== null) {
                    $acceptHeader = $request->getHeader('Accept');
                    $best = $negotiator->getBest($acceptHeader, $route['produces']);
                    if ($best === null) {
                        continue;
                    }
                    $produce = $best->getValue();
                }
            } else {
                if ($route['consumes'] !== null) {
                    $acceptHeader = $request->getHeader('Accept');
                    if ($negotiator->getBest($acceptHeader, $route['consumes']) === null) {
                        continue;
                    }
                }

                // si on définit @Produces, on se fiche des accepts
                if ($route['produces'] !== null) {
                    $produce = array_shift($route['produces']);
                }
            }

            array_shift($matches);
            $this->route->init(array(
                'resourceClass' => $route['classResource'],
                'methodClass' => $route['methodResource'],
                'idBean' => $route['idBean'],
                'uri' => $request->getUri(),
                'contentType' => $request->getContentType(),
                'method' => $request->getMethod(),
                'matches' => $matches,
                'produce' => $produce
            ));
        }

        return;
    }

    /**
     * Démarre l'analyse de la requête et le dispatch 
     * 
     * @param string $contextRoot nom
     * @throws WebApplicationException
     * @throws NotFoundResourceException
     * @throws InvalidResponseException
     */
    public function run($contextRoot = '') {
        $this->contextRoot = $contextRoot;

        $this->loadRoutes();
        $this->processRoute($this->request);

        /* @var $httpResponse \Huge\Rest\Http\HttpResponse */
        $httpResponse = null;

        try {
            if ($this->route->isInit()) {

                // vérifie si on sait produire le contentType
                if (!$this->webAppIoC->existsBodyWriter($this->route->getProduce())) {
                    throw new WebApplicationException('Le contentType "' . $this->route->getProduce() . '" n\'est pas géré', 406); //  Not Acceptable
                }
            } else {
                throw new NotFoundResourceException($this->request->getUri());
            }

            // analyse le contenu pour le parser
            if (IocArray::in_array($this->route->getMethod(), array('POST', 'PUT'))) {
                $bodyReaderClassName = $this->webAppIoC->getBodyReader($this->request->getContentType());
                if (($bodyReaderClassName !== null) && IocArray::in_array('Huge\Rest\Process\IBodyReader', class_implements($bodyReaderClassName))) {
                    $this->request->setEntity(Caller::statiq($bodyReaderClassName, 'read', array($this->request)));
                } else {
                    throw new WebApplicationException('Lecture de la requête impossible car "' . $bodyReaderClassName . '" implémente pas "Huge\Rest\Process\IBodyReader" ', 415);
                }
            }

            $beansRequestFilter = $this->webAppIoC->findBeansByImpl('Huge\Rest\Process\IRequestFilter');
            $filtersMapping = $this->webAppIoC->getRequestFiltersMapping();
            $filterCount = count($beansRequestFilter);
            for ($i = 0; $i < $filterCount; $i++) {
                $idBeanFilter = $beansRequestFilter[$i];
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
            $interceptorCount = count($beansInterceptor);
            for ($i = 0; $i < $interceptorCount; $i++) {
                $this->webAppIoC->getBean($beansInterceptor[$i])->start($this->request);
            }

            $httpResponse = Caller::instance($this->webAppIoC->getBean($this->route->getIdBean()), $this->route->getMethodClass(), $this->route->getMatches());

            if ($httpResponse === null) {
                throw new InvalidResponseException('La réponse HTTP ne doit pas être null');
            }
            // Application du mimeType pour la répone
            $httpResponse->setContentType($this->route->getProduce());
            // Write entity
            if ($httpResponse->hasEntity()) {
                $bodyWriterClassName = $this->webAppIoC->getBodyWriter($this->route->getProduce());
                if (($bodyWriterClassName !== null) && IocArray::in_array('Huge\Rest\Process\IBodyWriter', class_implements($bodyWriterClassName))) {
                    $httpResponse->body(Caller::statiq($bodyWriterClassName, 'write', array($httpResponse->getEntity())));
                } else {
                    throw new WebApplicationException('Ecriture de la requête impossible car "' . $bodyWriterClassName . '" implémente pas "Huge\Rest\Process\IBodyWriter" ', 406); //  Not Acceptable
                }
            }

            $beansResponseFilter = $this->webAppIoC->findBeansByImpl('Huge\Rest\Process\IResponseFilter');
            $filtersMapping = $this->webAppIoC->getResponseFiltersMapping();
            $filterCount = count($beansResponseFilter);
            for ($i = 0; $i < $filterCount; $i++) {
                $idBeanFilter = $beansResponseFilter[$i];
                if (isset($filtersMapping[$idBeanFilter])) {
                    if (preg_match('#' . $filtersMapping[$idBeanFilter] . '#', $this->request->getUri())) {
                        $this->webAppIoC->getBean($idBeanFilter)->doFilter($httpResponse);
                        break;
                    }
                } else {
                    $this->webAppIoC->getBean($idBeanFilter)->doFilter($httpResponse);
                }
            }

            for ($i = 0; $i < $interceptorCount; $i++) {
                $this->webAppIoC->getBean($beansInterceptor[$i])->end($httpResponse);
            }
        } catch (\Exception $e) {
            $exceptionMapperClassName = $this->webAppIoC->getExceptionMapper(get_class($e));
            $exceptionMapperClassName = $exceptionMapperClassName === null ? $this->webAppIoC->getExceptionMapper('Exception') : $exceptionMapperClassName;

            $exceptionMapper = $this->webAppIoC->getBean($exceptionMapperClassName);

            $impls = $exceptionMapperClassName === null ? array() : class_implements($exceptionMapper);
            if (($exceptionMapper !== null) && (IocArray::in_array('Huge\Rest\Process\IExceptionMapper', $impls))) {
                $httpResponse = Caller::instance($exceptionMapper, 'map', array($e));
            } else {
                $httpResponse = Http\HttpResponse::status(500);
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
