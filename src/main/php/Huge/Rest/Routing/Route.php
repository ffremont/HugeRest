<?php

namespace Huge\Rest\Routing;

use Huge\IoC\Annotations\Component;

/**
 * @Component
 */
class Route {
    
    /**
     *
     * @var boolean 
     */
    private $init;

    /**
     * Nom de la classe ressource
     * 
     * @var string
     */
    private $resourceClass;
    
    /**
     * Nom de la méthode de la classe ressource
     * 
     * @var string
     */
    private $methodClass;
    
    /**
     * URI de la requête 
     * Exemple : /monAppli/personne
     * 
     * @var string
     */
    private $uri;
    
    /**
     * Content-Type de la requête HTTP
     * 
     * @var string 
     */
    private $contentType;
    
    /**
     * Méthode HTTP de la requête
     * 
     * @var string
     */
    private $method;
    
    /**
     *
     * @var array 
     */
    private $matches;
    
    /**
     * Identifiant du bean ressource
     * 
     * @var string
     */
    private $idBean;
    
    function __construct() {
        $this->init = false;
    }
    
    public function init($values){
        $this->init = true;
        
        $this->resourceClass = $values['resourceClass'];
        $this->methodClass = $values['methodClass'];
        $this->uri = $values['uri'];
        $this->contentType = $values['contentType'];
        $this->method = $values['method'];
        $this->matches = $values['matches'];
        $this->idBean = $values['idBean'];
    }
    
    public function isInit() {
        return $this->init;
    }

    public function getResourceClass() {
        return $this->resourceClass;
    }

    public function getMethodClass() {
        return $this->methodClass;
    }

    public function getUri() {
        return $this->uri;
    }

    public function getContentType() {
        return $this->contentType;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getMatches() {
        return $this->matches;
    }

    public function getIdBean() {
        return $this->idBean;
    }



}

