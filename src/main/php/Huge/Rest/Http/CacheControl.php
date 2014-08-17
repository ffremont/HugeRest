<?php

namespace Huge\Rest\Http;

class CacheControl {

    private $private;
    private $noCache;
    private $noStore;
    private $noTransform;
    private $mustRevalidate;
    private $proxyRevalidate;
    
    private $maxAge;
    private $sMaxAge;
    
    public function __construct() {
        $this->private = false;
        $this->noCache = false;
        $this->noStore = false;
        $this->noTransform = true;
        $this->mustRevalidate = false;
        $this->proxyRevalidate = false;
        
        $this->maxAge = -1;
        $this->sMaxAge = -1;
    }

    /**
     * 
     * @return \Huge\Rest\Http\CacheControl
     */
    public static function noCache(){
        $cache = new CacheControl();
        $cache->setNoCache(true);
        $cache->setNoStore(true);
        
        return $cache;
    }
    
    /**
     * 
     * @param int $maxage
     * @return \Huge\Rest\Http\CacheControl
     */
    public static function maxAge($maxage){
        $cache = new CacheControl();
        $cache->setMaxAge($maxage);
        
        return $cache;
    }
    
    /**
     * Retourne la valeur du cache control
     * 
     * @return string
     */
    public function getValue(){
        $values = array();
        if($this->private){
            $values[] = 'private';
        }else{
            $values[] = 'public';
        }
        if($this->noCache){
            $values[] = 'no-cache';
        }
        if($this->noStore){
            $values[] = 'no-store';
        }        
        if($this->noTransform){
            $values[] = 'no-transform';
        }
        if($this->proxyRevalidate){
            $values[] = 'proxy-revalidate';
        }
        if($this->mustRevalidate){
            $values[] = 'must-revalidate';
        }
        if($this->maxAge !== -1){
            $values[] = 'max-age="'.$this->maxAge.'"';
        }
        if($this->sMaxAge !== -1){
            $values[] = 's-maxage="'.$this->sMaxAge.'"';
        }
        
        return implode(', ', $values);
    }
    
    public function isPrivate() {
        return $this->private;
    }

    public function isNoCache() {
        return $this->noCache;
    }

    public function isNoStore() {
        return $this->noStore;
    }

    public function isNoTransform() {
        return $this->noTransform;
    }

    public function isMustRevalidate() {
        return $this->mustRevalidate;
    }

    public function isProxyRevalidate() {
        return $this->proxyRevalidate;
    }
    
    public function getMaxAge() {
        return $this->maxAge;
    }

    public function getSMaxAge() {
        return $this->sMaxAge;
    }
    
    public function setPrivate($private) {
        $this->private = !!$private;
    }

    public function setNoCache($noCache) {
        $this->noCache = !!$noCache;
    }

    public function setNoStore($noStore) {
        $this->noStore = !!$noStore;
    }

    public function setNoTransform($noTransform) {
        $this->noTransform = $noTransform;
    }

    public function setMustRevalidate($mustRevalidate) {
        $this->mustRevalidate = !!$mustRevalidate;
    }

    public function setProxyRevalidate($proxyRevalidate) {
        $this->proxyRevalidate = !!$proxyRevalidate;
    }

    public function setMaxAge($maxAge) {
        $this->maxAge = $maxAge;
    }

    public function setSMaxAge($sMaxAge) {
        $this->sMaxAge = $sMaxAge;
    }
}

