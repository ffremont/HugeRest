<?php

namespace Huge\Rest\Http;

use Huge\Rest\Exceptions\ValidationException as RestValidationException;
use Huge\Rest\Exceptions\Models\Violation;
use Huge\IoC\Annotations\Component;
use Huge\IoC\Annotations\Autowired;
use Huge\IoC\Utils\IocArray;
use Fuel\Validation\Validator;
use Fuel\Validation\RuleProvider\FromArray;

/**
 * BodyReader permet de lire le contenu de la requête HTTP pour la valider et la transformer
 * 
 * @Component
 */
class BodyReader {

    /**
     * @Autowired("Huge\Rest\Http\HttpRequest")
     * @var \Huge\Rest\Http\HttpRequest
     */
    private $request;

    /**
     * @Autowired("Huge\Rest\WebAppIoC")
     * @var \Huge\Rest\WebAppIoC
     */
    private $webAppIoC;

    public function __construct() {
        
    }

    /**
     * Valide le contenu de l'objet / tableau
     * 
     * @throws \Huge\Rest\Exceptions\ValidationException
     */
    private function _checkEntity($validator, $entity) {
        $result = $validator->run(is_object($entity) ? (array) $entity : $entity);

        $errors = $result->getErrors();
        if (!empty($errors)) {
            $validations = array();
            foreach ($errors as $field => $message) {
                $validations[] = new Violation($field, $message);
            }

            throw new RestValidationException($validations, 'Validation impossible des données');
        }
    }

     /**
     * Permet de valider l'entity contenu dans la requête
     * 
     * @param string $validatorClassName
     * @param array $data
     * @throws Huge\Rest\Exceptions\ValidationException
     */
    private function _validate($validatorClassName, $data) {
        $impls = class_implements($validatorClassName);
        if (($this->request !== null) && ($data !== null) && IocArray::in_array('Huge\Rest\Data\IValidator', $impls)) {
            $generator = new FromArray();
            $validator = null;
            if ($this->webAppIoC->getFuelValidatorFactory() === null) {
                $validator = new Validator();
            } else {
                $validator = $this->webAppIoC->getFuelValidatorFactory()->createValidator();
            }
            $generator->setData(call_user_func_array($validatorClassName . '::getConfig', array()))->populateValidator($validator);

            $this->_checkEntity($validator, $data);
        }
    }
    
     /**
     * Permet de valider l'entity contenu dans la requête
     * 
     * @param string $validatorClassName
     * @throws Huge\Rest\Exceptions\ValidationException
     */
    public function validate($validatorClassName, $params) {
        $this->_validate($validatorClassName, $params);
    }

    /**
     * Permet de valider l'entity contenu dans la requête
     * 
     * @param string $validatorClassName
     * @throws Huge\Rest\Exceptions\ValidationException
     */
    public function validateEntity($validatorClassName) {
        $this->_validate($validatorClassName, $this->request->getEntity());
    }
    
    /**
     * Permet de valider une liste de modèles (array ou object)
     * 
     * @param string $validatorClassName nom de la classe qui implémente Huge\Rest\Data\IValidator
     * @throws \InvalidArgumentException
     * @throws \Huge\Rest\Exceptions\ValidationException
     */
    public function validateList($validatorClassName) {
        $impls = class_implements($validatorClassName);
        if (($this->request !== null) && ($this->request->getEntity() !== null) && IocArray::in_array('Huge\Rest\Data\IValidator', $impls)) {
            $generator = new FromArray();
            $validator = null;
            if ($this->webAppIoC->getFuelValidatorFactory() === null) {
                $validator = new Validator();
            } else {
                $validator = $this->webAppIoC->getFuelValidatorFactory()->createValidator();
            }
            $generator->setData(call_user_func_array($validatorClassName . '::getConfig', array()))->populateValidator($validator);

            if (is_array($this->request->getEntity())) {
                foreach($this->request->getEntity() as $model){
                    $this->_checkEntity($validator, $model);
                }
            }else{
                throw new \InvalidArgumentException('Entity doit être un array');
            }
        }
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest($request) {
        $this->request = $request;
    }

    public function getWebAppIoC() {
        return $this->webAppIoC;
    }

    public function setWebAppIoC(\Huge\Rest\WebAppIoC $webAppIoC) {
        $this->webAppIoC = $webAppIoC;
    }

}

