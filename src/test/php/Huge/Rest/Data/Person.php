<?php

namespace Huge\Rest\Data;

class Person implements IValidator {

    public function __construct() {
        
    }

    public static function getConfig() {
        return array(
            'nom' => array(
                'required' // Rules with no parameters can be specified like this
            ),
            'adresseEmail' => array(
                'required',
                'email'
            ),
        );
    }

}

