<?php

namespace Huge\Rest;

use Huge\IoC\Container\SuperIoC;
use Huge\IoC\Factory\ConstructFactory;
use Huge\IoC\Factory\SimpleFactory;
use Huge\IoC\Scope;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Annotations\AnnotationReader;

class WebAppIoC extends SuperIoC {

    /**
     * Cache utilisé pour l'exposition REST
     * 
     * @var \Doctrine\Common\Cache\Cache
     */
    private $apiCacheImpl;
    private $logger;

    /**
     * Liste des filtres qui implémente IFilter
     *      ID_BEAN_FILTRE => urlRegExp
     * 
     * @var array
     */
    private $filtersMapping;

    /**
     * Liste des exceptions et du mapper associé (implémente l'interface IExceptionMapper)
     *  Nom_classe_exception => Nom_classe_mapper
     * 
     * @var array
     */
    private $exceptionsMapping;
    
    /**
     * Vrai si le conteneur a déjà été initialisé
     * 
     * @var boolean
     */
    private $isStarted;

    public function __construct($version = '') {
        parent::__construct($version);

        $this->isStarted = false;
        $this->filtersMapping = array();
        $this->exceptionsMapping = array(
            'Huge\Rest\Exceptions\NotFoundException' => 'Huge\Rest\Exceptions\Mappers\NotFoundExceptionMapper'
        );
        $this->apiCacheImpl = null;
        $this->logger = \Logger::getLogger(__CLASS__);
        $this->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Api',
                'factory' => new ConstructFactory(array($this->apiCacheImpl), Scope::REQUEST)
            ),
            array(
                'class' => 'Huge\Rest\Http\HttpRequest',
                'factory' => new ConstructFactory(array($_SERVER))
            ),
            array(
                'class' => 'Huge\Rest\Routing\Route',
                'factory' => SimpleFactory::getInstance()
            ),
            array(
                'class' => 'Huge\Rest\Interceptors\PerfInterceptor',
                'factory' => SimpleFactory::getInstance()
            )
        ));
    }

    /**
     * Retourne les définitions des ressources
     * 
     * @return array
     */
    public function getResources() {
        $cacheKey = self::whoAmI() . md5(serialize($this->getDefinitions())) . $this->version . '_getResources';
        if ($this->apiCacheImpl !== null) {
            $resources = $this->cacheImpl->fetch($cacheKey);
            if ($resources !== FALSE) {
                return $resources;
            }
        }

        $resources = array();
        $definitions = $this->getDefinitions();
        $annotationReader = new AnnotationReader();
        foreach ($definitions as $definition) {
            $oResource = $annotationReader->getClassAnnotation(new \ReflectionClass($definition['class']), 'Huge\Rest\Annotations\Resource');
            if ($oResource !== null) {
                $resources[] = $definition['id'];
            }
        }

        if ($this->apiCacheImpl !== null) {
            $this->cacheImpl->save($cacheKey, $resources);
        }

        return $resources;
    }

    /**
     * Lance l'initialisation des conteneurs IoC
     */
    public function start() {
        if (!$this->isStarted) {
            parent::start();
            $this->isStarted = true;
        }
    }

    /**
     * Lance l'initialisation des conteneurs IoC et démarre l'analyse de la requête HTTP
     * 
     * @param $contextRoot ContextRoot de l'application s'il y en a un de spécifique
     */
    public function run($contextRoot = null) {
       $this->start();

        $api = $this->getBean('Huge\Rest\Api');
        if ($api === null) {
            $this->logger->error('Bean Huge\Rest\Api introuvable');
        } else {
            if($contextRoot !== null){
                $api->setContextRoot($contextRoot);
            }
            
            $api->run();
        }
    }

    public function getApiCacheImpl() {
        return $this->apiCacheImpl;
    }

    public function setApiCacheImpl(Cache $apiCacheImpl) {
        $this->apiCacheImpl = $apiCacheImpl;
    }

    public function getFiltersMapping() {
        return $this->filtersMapping;
    }

    public function addFiltersMapping(array $filtersMapping) {
        $this->filtersMapping = array_merge($this->filtersMapping, $filtersMapping);
    }

    public function getExceptionsMapping() {
        return $this->exceptionsMapping;
    }

    public function getExceptionMapper($exceptionClassName) {
        return isset($this->exceptionsMapping[$exceptionClassName]) ? $this->exceptionsMapping[$exceptionClassName] : null;
    }

    public function addExceptionsMapping(array $exceptionsMapping) {
        $this->exceptionsMapping = array_merge($this->exceptionsMapping, $exceptionsMapping);
    }

}

