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
           "huge/ioc": "..."
        }
    }
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
* Personnalisation du content type de la réponse : @Produces({"...", "..."})
* Personnalisation des contenus
  * interprétation du contenu de la requête  : implémentation de Huge\Rest\Process\IBodyReader
  * interprétation du contenu de la réponse  : implémentation de Huge\Rest\Process\IBodyWriter
* Gestion des erreurs extra-souple : implémentation de Huge\Rest\Process\IExceptionMapper
* Gestion de filtres sur les requêtes : implémentation de Huge\Rest\Process\IFilter
* Gestion d'intercepteurs sur les requêtes : implémentation de Huge\Rest\Process\IInterceptor
* Cache : basé sur doctrine cache
* Annotations basé sur doctrine annotations

## Création d'une ressource
* Utilisation des annotations :
    * @Resource obligatoire
    * @Path facultatif
    * @Consumes facultatif
    * @Produces facultatif
...

## Gérer un contenu de requête
* Pour gérer les types mime des requêtes HTTP vous avez la possibilité d'implémenter vos propres "IBodyReader"
* Interface à implémenter : Huge\Rest\Process\IBodyReader
```php
$ioc = new \Huge\Rest\WebAppIoC('1.0');
$ioc->addBodyReaders(array(
    'application/vnd.github.v1+json' => 'Huge\Rest\Process\Readers\JsonReader'
));
```
* Liste des readers disponibles
    * Huge\Rest\Process\Readers\JsonReader : décode le body avec json_decode 
    * Huge\Rest\Process\Readers\FormReader : décode le body avec urldecode
    * Huge\Rest\Process\Readers\TextReader : aucun traitement

## Gérer un contenu de réponse
* Une fonction d'une ressource retourne une instance de l'objet Huge\Rest\Http\HttpResponse. Cette dernière peut avoir l'attribut "entity" de valorisé qui sera à convertir en fonction du contentType souhaité de la réponse HTTP.
* Interface à implémenter : Huge\Rest\Process\IBodyWriter
```php
$ioc = new \Huge\Rest\WebAppIoC('1.0');
$ioc->addBodyWriters(array(
    'application/vnd.github.v1+json' => 'Huge\Rest\Process\Writers\JsonWriter'
));
```
* Liste des readers disponibles
    * Huge\Rest\Process\Writers\JsonWriter : encode $entity avec json_encode
## Filtrer les requêtes
...

## Intercepter les traitements REST
...

## Personnaliser les erreurs
- Interface à implémenter : Huge\Rest\Process\IExceptionMapper
- Enregistrement du mapping "Nom de l'exception" => "Nom de la classe qui implémente"
...


(en cours)
