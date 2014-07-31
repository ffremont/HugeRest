<?php

namespace Huge\Rest\Utils;

/**
 *  Classe d'aide pour traiter les paramètres de php.ini
 */
abstract class ConfigInitHelper {

    public function __construct() {
        
    }

    /**
     * Retourne le nombre d'octet à partir de la valeur stockée dans le php.ini
     * Exemple : 8M
     * 
     * @param type $value
     * @return int
     */
    public static function convertUnit($value) {
        $unit = substr($value, -1);
        if (!is_numeric($unit)) {
            $value = (int)substr($value, 0, -1);
        }

        // Convert to bytes
        switch (strtoupper($unit)) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }
        
        return $value;
    }

}

