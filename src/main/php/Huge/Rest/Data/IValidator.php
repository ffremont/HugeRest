<?php

namespace Huge\Rest\Data;

interface IValidator {
    
    /**
     * Retourne la configuration pour la validation de l'objet.
     * @see https://github.com/fuelphp/validation
     * 
     * @return array
     */
    public static function getConfig();
}
