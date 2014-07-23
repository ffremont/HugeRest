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

    public function __construct($server = array()) {
        $this->server = $server;
        $this->headers = $this->_getallheaders($server);
        $this->body = null;
        $this->entity = null;
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
     * /rest/aa/oo/oo => rest
     * 
     * @return string
     */
    public function getContextRoot(){
        $trim = trim($this->server['REQUEST_URI'], '/');
        $explode = explode('/', $trim);
        return count($explode) >= 1 ? $explode[0] : null;
    }
    
    public function getUri(){
        return trim($this->server['REQUEST_URI'], '/');
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
}

