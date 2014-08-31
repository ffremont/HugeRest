HugeRest
========

Framework PHP pour créer simplement, rapidement et efficacement une webapp REST
Exemple : https://github.com/ffremont/HugeRest-samples

##Installation
Installer avec composer
``` json
    {
        "require": {
           "huge/rest": "...",
           "doctrine/cache" : "v1.3.0"
        }
    }
```

.htaccess :
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ index.php [QSA,L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

```php
  $loader = require(__DIR__.'/../../../vendor/autoload.php');
  
  // nécessaire charger les annotations
  \Huge\IoC\Container\SuperIoC::registerLoader(array($loader, 'loadClass'));
```

## Fonctionnalités
* Définition des ressources et des chemins via : @Resource / @Path("CHEMIN")
* Gestion des méthodes HTTP : @Get, @Put, @Post, @Delete
* Personnalisation des types mimes : @Consumes({"...", "..."})
    * Permet de définir les accepts (GET, Delete) ou le content-type (POST, PUT)
* Personnalisation du content type de la réponse : @Produces({"...", "..."})
* Personnalisation des contenus
  * interprétation du contenu de la requête  : implémentation de Huge\Rest\Process\IBodyReader
  * interprétation du contenu de la réponse  : implémentation de Huge\Rest\Process\IBodyWriter
  * validation des contenus l'interface : Huge\Rest\Data\IValidator
* Gestion des erreurs extra-souple : implémentation de Huge\Rest\Process\IExceptionMapper
* Gestion de filtres sur les requêtes : implémentation de Huge\Rest\Process\IFilter
* Gestion d'intercepteurs sur les requêtes : implémentation de Huge\Rest\Process\IInterceptor
* Cache : basé sur doctrine cache
* Annotations basé sur doctrine annotations

## Configuration
```php
$ioc = new \Huge\Rest\WebAppIoC('1.1', array(
    'maxBodySize' => 1024 // taille max en octet des body (par défaut ini_get('post_max_size')). Un flux Json en PUT / POST ne pourra pas faire + d'1Ko dans cet exemple
));
```

## Création d'une ressource
* Une ressource REST se matérialise par une classe PHP annotée. C'est un composant au sens Huge\IoC.
* Utilisation des annotations :
    * @Resource obligatoire
    * @Path facultatif
    * @Consumes facultatif
        * Si POST, PUT cela correspond au contentType de la requête
        * Sinon, cela correspond à l'entête accept de la requête
    * @Produces facultatif
        * Définition du typeMime de sortie
        * Si POST, PUT cela correspond à l'entête accept de la requête
        * Sinon, cela correspond au contentType de la réponse
    
* Les @Path sont des regexp
    * les chaînes trouvées sont ajoutées en paramètres de la fonction
* Liste des tokens :
    * ':mString' => '([a-zA-Z]+)'
    * ':mNumber' => '([0-9]+)'
    * ':mAlpha' => '([a-zA-Z0-9-_]+)'
    * ':oString' => '([a-zA-Z]*)'
    * ':oNumber' => '([0-9]*)'
    * ':oAlpha' => '([a-zA-Z0-9-_]*)'
    
```php
/**
 * EXEMPLE
 * Ressource "Person" qui a pour chemin "person". Notre ressource produit en retour une structure JSON en v1 par défaut. 
 * Chaque opération de la classe prend par défaut du "application/vnd.person.v1+json" / "application/json".
 * Si on surcharge sur la fonction @Consumes / @Produces alors la configuration de la fonction primera.
 * 
 * @Component
 * @Resource
 * @Path("person")
 * 
 * @Consumes({"application/vnd.person.v1+json", "application/json"})
 * @Produces({"application/vnd.person.v1+json"})
 */
class Person {

    /**
     * @Autowired("Huge\Rest\Http\HttpRequest")
     * @var \Huge\Rest\Http\HttpRequest
     */
    private $request;
    
     /**
     * @Autowired("Huge\IoC\Factory\ILogFactory")
     * @var \Huge\IoC\Factory\ILogFactory
     */
    private $loggerFactory;
   
    public function __construct() {}

    /**
     * @Get
     * @Consumes({"text/plain"})
     * @Produces({"text/plain"})
     */
    public function ping() {        
        return HttpResponse::ok();
    }

    /**
     * @Get
     * @Path(":mNumber")
     */
    public function get($id = '') {
        $person = new \stdClass();
        $person->id = $id;

        return HttpResponse::ok()->entity($person);
    }
    
    /**
     * @Delete
     * @Path(":mNumber")
     */
    public function delete($id = '') {
        $person = new \stdClass();
        $person->id = $id;
        
        return HttpResponse::ok()->entity($person);
    }
    
     /**
     * @Put
     * @Path(":mNumber")
     */
    public function put($id = '') {
        // @Consumes retenu est celui de la classe (du json)
        $requestBody = (object)$this->request->getEntity();
        $requestBody->id = $id;
        
        return HttpResponse::ok()->entity($requestBody);
    }

    /**
     * Accepte le content-type application/json
     * @Post
     */
    public function post() {
        $person = new \stdClass();
        $person->id = uniqid();
        
        return HttpResponse::ok()->code(201)->entity($person);
    }

    /**
     * @Get
     * @Path("search/?:oNumber/?:oNumber")
     */
    public function search($numberA = '', $numberB = '') {
        $query = $this->request->getParam('query');

        $list = array();
        for ($i = 0; $i < 5; $i++) {
            $person = new \stdClass();
            $person->id = uniqid();
            $person->query = $query;
            $person->a = $numberA;
            $person->b = $numberB;
            $list[] = $person;
        }
        
        return HttpResponse::ok()->entity($list);
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest($request) {
        $this->request = $request;
    }
    
    public function getLoggerFactory() {
        return $this->loggerFactory;
    }

    public function setLoggerFactory(\Huge\IoC\Factory\ILogFactory $loggerFactory) {
        $this->loggerFactory = $loggerFactory;
    }
}
```

## Gérer un contenu de requête
* Pour gérer les types mime des requêtes HTTP vous avez la possibilité d'implémenter vos propres "IBodyReader"
* Interface à implémenter : Huge\Rest\Process\IBodyReader
```php
$ioc = new \Huge\Rest\WebAppIoC('1.0');
$ioc->addBodyReaders(array(
    'application/vnd.github.v1+json' => 'Huge\Rest\Process\Readers\JsonReader'
));
```
* Liste et configuration des readers disponibles (l'instance HttpResquet = $r)
    * 'application/x-www-form-urlencoded' => 'Huge\Rest\Process\Readers\FormReader', => $r->getBody() : $_REQUEST
    * 'application/json' => 'Huge\Rest\Process\Readers\JsonReader', => $r->getBody() : json_decode
    * 'text/plain' => 'Huge\Rest\Process\Readers\TextReader', => $r-getBody() => au body de la request
    * 'multipart/form-data' => 'Huge\Rest\Process\Readers\UploadReader', => $r->getBody() : instance Huge\Rest\Http\HttpFiles
    * 'multipart/octet-stream' => 'Huge\Rest\Process\Readers\UploadReader', // idem
    * 'application/octet-stream' => 'Huge\Rest\Process\Readers\BinaryReader' => $r->getBody() : instance Huge\Rest\Data\TempFile

## Gérer un contenu de réponse
* Une fonction d'une ressource retourne une instance de l'objet Huge\Rest\Http\HttpResponse. Cette dernière peut avoir l'attribut "entity" de valorisé qui sera à convertir en fonction du contentType souhaité de la réponse HTTP.
* Interface à implémenter : Huge\Rest\Process\IBodyWriter
```php
$ioc = new \Huge\Rest\WebAppIoC('1.0');
$ioc->addBodyWriters(array(
    'application/vnd.github.v1+json' => 'Huge\Rest\Process\Writers\JsonWriter'
));
```
* Liste et configurations des writers disponibles
    * 'application/x-www-form-urlencoded' => 'Huge\Rest\Process\Writers\FormWriter', => encode $entity avec urlencode
    * 'application/json' => 'Huge\Rest\Process\Writers\JsonWriter', => encode $entity avec json_encode
    * 'text/plain' => 'Huge\Rest\Process\Writers\TextWriter' => caste en string
    

## Filtrer les requêtes et réponses
* Les filtres permettent d'exercer des contrôles avant les traitements REST. Un filtre est un composant au sens Huge\IoC.
* Interface à implémenter : Huge\Rest\Process\IRequestFilter
* Interface à implémenter : Huge\Rest\Process\IResponseFilter
```php
$ioc = new \Huge\Rest\WebAppIoC('1.0');
$ioc->addDefinitions(array(
    array(
        'class' => 'MyWebApi\Security\Authorization',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),array(
        'class' => 'MyWebApi\Security\AuthorizationBis',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),array(
        'class' => 'MyWebApi\PowerByFilter',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    )
));
$ioc->addRequestFiltersMapping(array(
    'MyWebApi\Security\Authorization' => '.*', /* applique le filtre sur toutes les ressources */
    'MyWebApi\Security\AuthorizationBis' /* on ne tient pas compte des paths */
));
$ioc->addResponseFiltersMapping(array(
    'MyWebApi\PowerByFilter' => '.*'
));
```

## Intercepter les traitements REST
* Pour différentes raisons vous aurez besoin de connaître le début et la fin des traitements de votre API. Un intercepteur est un composant au sens Huge\IoC.
* Interface à implémenter : Huge\Rest\Process\IInterceptor
```php
$ioc = new \Huge\Rest\WebAppIoC('1.0');
$ioc->addDefinitions(array(
    array(
        'class' => 'MyWebApi\Interceptors\Custom',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    )
));
```

## Validateurs sur les modèles
* Basé sur fuelphp validation https://github.com/fuelphp/validation
* Il est possible de valider les données qui sont passées dans le body de la requête.
* Interface que le modèle doit implémenter : Huge\Rest\Data\IValidator
    ```php
    // dans votre classe ressource
    /**
    * @Autowired("Huge\Rest\Http\BodyReader")
    */
    private $bodyReader;
    
    // dans votre fonction
    $this->bodyReader->validateEntity('...nom_de_la_classe_modele...');
    // ou
    $this->bodyReader->validateEntityList('...nom_de_la_classe_modele...'); // si le contenu est une liste
    ```
    * Lancement de l'exception : Huge\Rest\Exceptions\ValidationException

* Personnalisation du validateur fuelPhp \Huge\Rest\Data\IFuelValidatorFactory
     ```php
        $webAppIoC->setFuelValidatorFactory($votre_factory)
    ```

## Personnaliser les erreurs
* Votre webapp va pouvoir emettre des exceptions qu'il va falloir convertir en réponse HTTP. Pour réaliser cela, il va être nécessaire d'enregistrer des couples selon le format : "Nom de l'exception" => "Nom de la classe qui implémente".
* Interface à implémenter : Huge\Rest\Process\IExceptionMapper
* Il est possible de définir un mapper d'exceptions par défaut "Exception" => "MonMapper"
```php
$ioc = new \Huge\Rest\WebAppIoC('1.0');
$ioc->addDefinitions(array(
    array(
        'class' => 'MyWebApi\Exceptions\LogicMapper',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ) // définition des autres composants qui implémentes IExceptionMapper...
));
$ioc->addExceptionsMapping(array(
    'LogicException' => 'MyWebApi\Exceptions\LogicMapper',
    'Huge\Rest\Exceptions\NotFoundResourceException' => null, // désactivation du mapper
    'Exception' => 'MyWebApi\Exceptions\DefaultExceptionMapper'
));
```
* Liste des mappers :
    * 'Huge\Rest\Exceptions\NotFoundResourceException' => 'Huge\Rest\Exceptions\Mappers\NotFoundResourceExceptionMapper',
    * 'Huge\Rest\Exceptions\InvalidResponseException' => 'Huge\Rest\Exceptions\Mappers\InvalidResponseExceptionMapper',
    * 'Huge\Rest\Exceptions\ValidationException' => 'Huge\Rest\Exceptions\Mappers\ValidationExceptionMapper',
    * 'Huge\Rest\Exceptions\WebApplicationException' => 'Huge\Rest\Exceptions\Mappers\WebApplicationExceptionMapper',
    * 'Huge\Rest\Exceptions\SizeLimitExceededException' => 'Huge\Rest\Exceptions\Mappers\SizeLimitExceededExceptionMapper',
    * 'Exception' => 'Huge\Rest\Exceptions\Mappers\DefaultExceptionMapper'

## Logger
* Implémenter la factory : Huge\IoC\Factory\ILogFactory
* Ajouter le composant dans votre conteneur de plus haut niveau
    * Dans le cas où vous avez * conteneurs et que chacun dispose de son implémentation. L'injection (@Autowired de ILogFactory) ne marchera pas car * implémentations seront détectées.
    * Généralement, le conteneur WebApp contient l'implémentation et les classes des tests
* Logger factory (composant) vide : Huge\Rest\Log\NullLoggerFactory

## Ordonnancement
* Analyse de la requête HTTP
    * à partir du composant Huge\Rest\Http\HttpRequest
    * détermination d'une route : Huge\Rest\Routing\Route (composant)
    * si aucune route n'existe, lancement de Huge\Rest\Exceptions\NotFoundResourceException
* Analyse du contenu de la requête (POST ou PUT)
    * utilisation des IBodyReader
* Exécution des Huge\Rest\Process\IRequestFilter
* Exécution de la fonction start des intercepteurs Huge\Rest\Process\IInterceptor
* EXECUTION DU TRAITEMENT LIE A LA RESSOURCE
* Détermination du contentType à appliquer dans la réponse HTTP
    * utilisation des IBodyWriter
* Exécution des Huge\Rest\Process\IResponseFilter
* Exécution de la fonction end des intercepteurs Huge\Rest\Process\IInterceptor
* Construction de la réponse : Huge\Rest\Http\HttpResponse (fonction build)

## Limitations
* La gestion des erreurs ne permet pas d'exploiter l'héritage des exceptions
* Logger basé sur l'interface Psr\Log : https://packagist.org/packages/psr/log
* Basé sur Huge\IoC
* Validateur basé sur fuel validation

## Tests
* Tests unitaires : phpunit -c src/test/resources/phpunit.xml --testsuite TU
* Tests d'intégration avec apache2 sur src/test/webapp : phpunit -c src/test/resources/phpunit.xml --testsuite IT

## Performances
Voici des petits tests sur ma modeste machine PHP5.3):
Sans ApcCache, ni memcache
2014-07-29 13:00 - Huge\Rest\Interceptors\PerfInterceptor INFO  : Temps d'exécution de la requête pendant 4.28 ms
2014-07-29 13:00 - Huge\Rest\Interceptors\PerfInterceptor INFO  : Consommation de 3.5 mo, avec un pic à 3.77 mo

Avec ApcCache, sans memcache
2014-07-29 13:04 - Huge\Rest\Interceptors\PerfInterceptor INFO  : Temps d'exécution de la requête pendant 3.87 ms
2014-07-29 13:04 - Huge\Rest\Interceptors\PerfInterceptor INFO  : Consommation de 1.6 mo, avec un pic à 1.8 mo

Avec ApcCache, avec memcache
2014-07-29 13:04 - Huge\Rest\Interceptors\PerfInterceptor INFO  : Temps d'exécution de la requête pendant 1.27 ms
2014-07-29 13:04 - Huge\Rest\Interceptors\PerfInterceptor INFO  : Consommation de 1.6 mo, avec un pic à 1.79 mo
