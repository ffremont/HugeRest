<?php

namespace Huge\Rest\Data;


interface IFuelValidatorFactory {
    /**
     * Retourne une instance du validateur FuelPhp
     * 
     * @return \Fuel\Validation\Validator
     */
    public function createValidator();
}

?>
