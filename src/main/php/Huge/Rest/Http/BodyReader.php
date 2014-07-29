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

    public function __construct() {}

    /**
     * Permet de valider les données présentent dans le body de la requête
     * 
     * @param string $validatorClassName
     * @throws RestValidationException
     */
    public function validate($validatorClassName) {
        $impls = class_implements($validatorClassName);
        if (($this->request !== null) && ($this->request->getEntity() !== null) && IocArray::in_array('Huge\Rest\Data\IValidator', $impls)) {
            $generator = new FromArray();
            $validator = null;
            if($this->webAppIoC->getFuelValidatorFactory() === null){
                $validator = new Validator();
            }else{
                $validator = $this->webAppIoC->getFuelValidatorFactory()->createValidator();
            }
            $generator->setData(call_user_func_array($validatorClassName . '::getConfig', array()))->populateValidator($validator);
            $result = $validator->run(is_object($this->request->getEntity()) ? (array) $this->request->getEntity() : $this->request->getEntity());

            $errors = $result->getErrors();
            if (!empty($errors)) {
                $validations = array();
                foreach ($errors as $field => $message) {
                    $validations[] = new Violation($field, $message);
                }

                throw new RestValidationException($validations, 'Validation impossible des données');
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

