<?php

namespace Huge\Rest\Http;

use Huge\Rest\Exceptions\UploadException;

class HttpFile {

    /**
     * Correspond  au champ dans $_FILES
     * 
     * @var array
     */
    private $file;

    /**
     * Permet de créer une représentation du fichier
     * 
     * @param string $fileName
     * @param array $aData
     * @param int $limit taille max en octet du fichier (gestion applicative)
     * 
     * @throws \InvalidArgumentException
     * @throws \Huge\Rest\Exceptions\UploadException
     */
    public function __construct($aData = array(), $limit = null) {
        $this->file = $aData;
        
        if( ($limit !== null) && ($this->getSize() > $limit) ){
            throw new UploadException('Fichier trop important, la limite est à '.$limit, 100);
        }
        
        if(!$this->isUploaded()){
            throw new UploadException(self::codeToMessage($this->getCode()), $this->getCode());
        }
    }
    
    /**
     * Traduction des codes
     * @see http://php.net/manual/fr/features.file-upload.errors.php
     * 
     * @param int $code
     * @return string
     */
    private static function codeToMessage($code) {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }

    /**
     *
     * @return string
     */
    public function getName() {
        return $this->file['name'];
    }

    /**
     *
     * @return string
     */
    public function getTmpFile() {
        return $this->file['tmp_name'];
    }
    
    /**
     * Retourne le contenu du fichier sous la forme d'une chaîne
     * 
     * @return string
     */
    public function getContents(){
        return file_get_contents($this->getTmpFile());
    }
    
    /**
     * Retourne le code de l'upload
     * @see http://php.net/manual/fr/features.file-upload.errors.php
     * 
     * @return int
     */
    public function getCode(){
        return $this -> file['error'];
    }

    /**
     *
     * @return boolean
     */
    public function isUploaded() {
        return isset($this->file['error']) ? $this->file['error']  == 0 : false;
    }

    /**
     *
     * @return string
     */
    public function getExtension() {
        $nom = $this->file['name'];

        $tab = explode('.', $nom);
        return count($tab) >= 2 ? $tab[1] : null;
    }

    public function getSize() {
        return (int) $this->file['size'];
    }

}

