<?php

namespace Huge\Rest\Http;

use Huge\Rest\Exceptions\SizeLimitExceededException;
use Huge\Rest\Exceptions\IOException;
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
    private $params;
    private $uri;
    private $maxBodySize;

    /**
     * 
     * @param array $server
     * @param array $request
     * @param int $maxBodySize taille max du body de la requête (en octet)
     */
    public function __construct($maxBodySize = null) {
        $this->server = $_SERVER;
        $this->headers = $this->_getallheaders($this->server);
        $this->body = null;
        $this->entity = null;
        $this->accepts = null;
        $this->params = $_REQUEST;
        $this->maxBodySize = $maxBodySize;

        $uriTrim = trim($this->server['REQUEST_URI'], '/');
        $matches = array();
        if (preg_match('#[^\?]*#', $uriTrim, $matches)) {
            $this->uri = $matches[0];
        } else {
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
        if (isset($server['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $server['CONTENT_TYPE'];
        }

        return $headers;
    }

    /**
     * Retourne la liste des accept (mode lazy)
     * 
     * @return array
     */
    public function getAccepts() {
        if ($this->accepts === null) {
            $matchesAccepts = array();
            if (preg_match("#[^;]+#", $this->getHeader('Accept'), $matchesAccepts)) {
                $this->accepts = explode(',', $matchesAccepts[0]);
            } else {
                $this->accepts = array();
            }
        }

        return $this->accepts;
    }

    public function getUri() {
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

    public function getContentType() {
        $contentType = $this->getHeader('Content-Type');
        if(strpos($contentType, ';') !== false){
            $matches = array();
            if (preg_match("#[^;]+#", $contentType, $matches)) {
                $contentType = $matches[0];
            } 
        }
        
        return $contentType;
    }

    /**
     * Lit le body de la requête en tenant compte de la limitation s'il y en a une "maxBodySize"
     * 
     * @param int $threshold lecture des données par bloc de X octets
     * @return mixed
     * @throws SizeLimitExceededException
     * @throws IOException
     */
    public function getBody($threshold = 1024) {
        if ($this->body === null) {
            $resource = fopen("php://input", "r");
            if ($resource === false) {
                throw new IOException('Impossible d\'ouverture du flux "php://input" en lecture');
            }
            $chunk_read = 0;
            $this->body = '';

            /* Lecture des données, $threshold à la fois */
            while ($data = fread($resource, $threshold)) {
                $chunk_read = $chunk_read + strlen($data);

                if (($this->maxBodySize !== null) && ($chunk_read > $this->maxBodySize)) {
                    fclose($resource);
                    throw new SizeLimitExceededException('Taille du flux HTTP invalide', $this->maxBodySize, $chunk_read);
                }

                $this->body .= $data;
            }

            /* Fermeture du flux */
            fclose($resource);
        }

        return $this->body;
    }

    /**
     * 
     * @return resource
     */
    public function getBodyResource() {
        return fopen("php://input", "r");
    }

    public function getEntity() {
        return $this->entity;
    }

    public function setEntity($entity) {
        $this->entity = $entity;
    }

    /**
     * Cache Control de la requêter
     *
     * @return string
     */
    public function getCacheControl() {
        return (string) $this->getHeader('Cache-Control');
    }

    /**
     * Retourne un paramètre
     * 
     * @param string $name
     * @return mixed
     */
    public function getParam($name) {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    public function getParams() {
        return $this->params;
    }

    public function getMaxBodySize() {
        return $this->maxBodySize;
    }

}

