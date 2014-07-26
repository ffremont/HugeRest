<?php

namespace Huge\Rest\Http;

use Huge\IoC\Annotations\Component;

/**
 * @Component
 */
class HttpRequest {

    private $headers;
    private $server;
    private $body;
    private $entity;
    private $accepts;
    private $get;
    private $uri;

    public function __construct($server = array(), $get = array()) {
        $this->server = $server;
        $this->headers = $this->_getallheaders($server);
        $this->body = null;
        $this->entity = null;
        $this->accepts = null;
        $this->get = array();
        
        $uriTrim = trim($this->server['REQUEST_URI'], '/');
        $matches = array();
        if(preg_match('#[^\?]*#', $uriTrim, $matches)){
            $this->uri = $matches[0];
        }else{
            $this->uri = $uriTrim;
        }
    }

    private function _getallheaders($server) {
        $headers = '';
        foreach ($server as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        if(isset($server['CONTENT_TYPE'])){
            $headers['Content-Type'] = $server['CONTENT_TYPE'];
        }
        
        return $headers;
    }
    
    /**
     * Retourne la liste des accept (mode lazy)
     * 
     * @return array
     */
    public function getAccepts(){
        if($this->accepts === null){
            $matchesAccepts = array();
             if(preg_match("#[^;]+#", $this->getHeader('Accept'), $matchesAccepts)){
                 $this->accepts = explode(',', $matchesAccepts[0]);
             }else{
                 $this->accepts = array();
             }
        }
        
        return $this->accepts;
    }
    
    /**
     * /rest/aa/oo/oo => rest
     * 
     * @return string
     */
    public function getContextRoot(){
        $explode = explode('/', $this->uri);
        return count($explode) >= 1 ? $explode[0] : null;
    }
    
    public function getUri(){
        return $this->uri;
    }

    public function getMethod() {
        return isset($this->server['REQUEST_METHOD']) ? strtoupper($this->server['REQUEST_METHOD']) : null;
    }

    public function getIp() {
        $ipSource = isset($this->server['REMOTE_ADDR']) ? $this->server['REMOTE_ADDR'] : null;
        if (isset($this->server['HTTP_X_FORWARDED_FOR'])) {
            $ipSource = $this->server['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($this->server['HTTP_X_REAL_IP'])) {
            $ipSource = $this->server['HTTP_X_REAL_IP'];
        }

        return $ipSource;
    }

    public function getHeader($name) {
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }
    
    public function getContentType(){
        return $this->getHeader('Content-Type');
    }
    
    /**
     * Retourne le contenu de la requête en mode lazy
     * 
     * @return sring
     */
    public function getBody() {
        if($this->body === null){
            $this->body = file_get_contents("php://input");
        }
        
        return $this->body;
    }
    
    public function getEntity() {
        return $this->entity;
    }

    public function setEntity($entity) {
        $this->entity = $entity;
    }
    
    public function getGet() {
        return $this->get;
    }

    public function setGet($get) {
        $this->get = $get;
    }
    
    /**
     * Retourne un paramètre GET
     * 
     * @param string $name
     * @return mixed
     */
    public function getParamGet($name){
        return isset($this->get[$name]) ? $this->get[$name] : null;
    }
    
}

