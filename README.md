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
Interface à implémenter : Huge\Rest\Process\IBodyReader
...

## Gérer un contenu de réponse
Interface à implémenter : Huge\Rest\Process\IBodyWriter
...

## Filtrer les requêtes
...

## Intercepter les traitements REST
...

## Personnaliser les erreurs
- Interface à implémenter : Huge\Rest\Process\IExceptionMapper
- Enregistrement du mapping "Nom de l'exception" => "Nom de la classe qui implémente"
...


(en cours)
