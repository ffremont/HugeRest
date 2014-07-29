<?php

namespace Huge\Rest;

use Huge\IoC\Annotations\Component;
use Huge\IoC\Container\SuperIoC;
use Huge\IoC\Factory\ConstructFactory;
use Huge\IoC\Factory\SimpleFactory;
use Huge\IoC\Scope;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Annotations\AnnotationReader;
use Huge\Rest\Data\IFuelValidatorFactory;

/**
 * Conteneur IoC permettant de gérer l'exposition REST
 * 
 * @see https://github.com/ffremont/HugeRest
 * @Component
 */
class WebAppIoC extends SuperIoC {

    /**
     * Cache utilisé pour l'exposition REST
     * 
     * @var \Doctrine\Common\Cache\Cache
     */
    private $apiCacheImpl;

    /**
     *
     * @var \Logger
     */
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
     * Liste des IBodyReader
     * 
     * @var array
     */
    private $bodyReaders;

    /**
     * Liste des IBodyWriter
     *
     * @var array
     */
    private $bodyWriters;

    /**
     * Vrai si le conteneur a déjà été initialisé
     * 
     * @var boolean
     */
    private $isStarted;

    /**
     *
     * @var \Huge\Rest\Data\IFuelValidatorFactory
     */
    private $fuelValidatorFactory;

    /**
     * 
     * @param string $version permet de rafraichir le cache lors des montées de versions
     */
    public function __construct($version = '') {
        parent::__construct($version);

        $this->isStarted = false;
        $this->fuelValidatorFactory = null;
        $this->filtersMapping = array();
        $this->bodyReaders = array(
            'application/x-www-form-urlencoded' => 'Huge\Rest\Process\Readers\FormReader',
            'application/json' => 'Huge\Rest\Process\Readers\JsonReader',
            'text/plain' => 'Huge\Rest\Process\Readers\TextReader'
        );
        $this->bodyWriters = array(
            'application/x-www-form-urlencoded' => 'Huge\Rest\Process\Writers\FormWriter',
            'application/json' => 'Huge\Rest\Process\Writers\JsonWriter',
            'text/plain' => 'Huge\Rest\Process\Writers\TextWriter'
        );
        $this->exceptionsMapping = array(
            'Huge\Rest\Exceptions\NotFoundResourceException' => 'Huge\Rest\Exceptions\Mappers\NotFoundResourceExceptionMapper',
            'Huge\Rest\Exceptions\BadImplementationException' => 'Huge\Rest\Exceptions\Mappers\BadImplementationExceptionMapper',
            'Huge\Rest\Exceptions\InvalidResponseException' => 'Huge\Rest\Exceptions\Mappers\InvalidResponseExceptionMapper',
            'Huge\Rest\Exceptions\ValidationException' => 'Huge\Rest\Exceptions\Mappers\ValidationExceptionMapper'
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
                'factory' => new ConstructFactory(array($_SERVER, $_GET))
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
    public function getResources() {
        $cacheKey = self::whoAmI() . md5(serialize($this->getDefinitions())) . $this->version . '_getResources';
        if ($this->apiCacheImpl !== null) {
            $resources = $this->apiCacheImpl->fetch($cacheKey);
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
            $this->apiCacheImpl->save($cacheKey, $resources);
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
            if ($contextRoot !== null) {
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

    /**
     *  Ajout des filtres de type intercepteur sur les requêtes HTTP
     *      ID_BEAN (implémente IInterceptor) => str_reg_exp_uri
     * 
     * @param array $filtersMapping
     */
    public function addFiltersMapping(array $filtersMapping) {
        $this->filtersMapping = array_merge($this->filtersMapping, $filtersMapping);
    }

    public function getExceptionsMapping() {
        return $this->exceptionsMapping;
    }

    /**
     * Retourne le nom de la classe mapper qui va traiter le mapping de l'exception donnée
     * 
     * @param string $exceptionClassName
     * @return string
     */
    public function getExceptionMapper($exceptionClassName) {
        return isset($this->exceptionsMapping[$exceptionClassName]) ? $this->exceptionsMapping[$exceptionClassName] : null;
    }

    /**
     * Ajout des mappers sur les exceptions
     *      nom_de_la_classe_exception => nom_de_la_classe (implémente IExceptionMapper)
     * 
     * @param array $exceptionsMapping
     */
    public function addExceptionsMapping(array $exceptionsMapping) {
        $this->exceptionsMapping = array_merge($this->exceptionsMapping, $exceptionsMapping);
    }

    public function getRequestParsers() {
        return $this->bodyReaders;
    }

    /**
     * Ajout des parser pour analyser le contenu des requête HTTP
     *      contentType(MIME Type) => nom_de_la_classe (implémente IBodyReader)
     * 
     * @param array $bodyReaders
     */
    public function addBodyReaders(array $bodyReaders) {
        $this->bodyReaders = array_merge($this->bodyReaders, $bodyReaders);
    }

    /**
     * Retourne le nom de la classe qui implémente le IBodyReader pour analye le contenu de la requête HTTP
     * 
     * @param string $contentType
     * @return string
     */
    public function getBodyReader($contentType) {
        return isset($this->bodyReaders[$contentType]) ? $this->bodyReaders[$contentType] : null;
    }

    /**
     *  Retourne le nom de la classe qui implémente IBodyWriter
     * 
     * @param string $mimeType
     * @return string
     */
    public function getBodyWriter($mimeType) {
        return isset($this->bodyWriters[$mimeType]) ? $this->bodyWriters[$mimeType] : null;
    }

    /**
     * Merge les bodyWriters
     *      TypeMime => nom_de_la_classe (implémente IBodyWriter)
     * 
     * @param array $bodyWriters
     */
    public function addBodyWriters(array $bodyWriters) {
        $this->bodyWriters = array_merge($this->bodyWriters, $bodyWriters);
    }

    /**
     * 
     * @return \Huge\Rest\Data\IFuelValidatorFactory
     */
    public function getFuelValidatorFactory() {
        return $this->fuelValidatorFactory;
    }

    /**
     * 
     * @param \Huge\Rest\Data\IFuelValidatorFactory $fuelValidatorFactory
     */
    public function setFuelValidatorFactory(IFuelValidatorFactory $fuelValidatorFactory) {
        $this->fuelValidatorFactory = $fuelValidatorFactory;
    }

}

