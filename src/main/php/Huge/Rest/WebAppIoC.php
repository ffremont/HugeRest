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
     *      ID_BEAN_FILTRE => array( urlRegExp1, urlRegExp2 )
     * 
     * @var array
     */
    private $filtersMapping;

    public function __construct($version = '') {
        parent::__construct($version);

        $this->filtersMapping = array();
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
            )
        ));
    }
    
    /**
     * Retourne les définitions des ressources
     * 
     * @return array
     */
    public function getResources(){
        $cacheKey = self::whoAmI().md5(serialize($this->getDefinitions())).$this->version.'_getResources';
        if ($this->apiCacheImpl !== null) {
            $resources = $this->cacheImpl->fetch($cacheKey);
            if ($resources !== FALSE) {
                return $resources;
            }
        }
        
        $resources = array();
        $definitions = $this->getDefinitions();
        $annotationReader = new AnnotationReader();
        foreach($definitions as $definition){
            $oResource = $annotationReader->getClassAnnotation(new \ReflectionClass($definition['class']), 'Huge\Rest\Annotations\Resource');
            if($oResource !== null){
                $resources[] = $definition['id'];
            }
        }
        
        if ($this->apiCacheImpl !== null) {
            $this->cacheImpl->save($cacheKey, $resources);
        }
        
        return $resources;
    }

    public function start() {
        parent::start();

        $api = $this->getBean('Huge\Rest\Api');
        if($api === null){
            $this->logger->error('Bean Huge\Rest\Api introuvable');
        }else{
            $this->getBean('Huge\Rest\Api')->run();
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

    public function setFiltersMapping(array $filtersMapping) {
        $this->filtersMapping = $filtersMapping;
    }
}

