<?php

namespace Huge\Rest\Process\Readers;

use Huge\Rest\Process\IBodyReader;
use Huge\Rest\Exceptions\IOException;
use Huge\Rest\Data\TempFile;

class BinaryReader implements IBodyReader {

    const THRESHOLD = 10240;
    
    public function __construct() {
        
    }

    /**
     * 
     * @param \Huge\Rest\Http\HttpRequest $request
     * @throws IOException
     * @throws SizeLimitExceededException
     * @return \Huge\Rest\Data\TempFile
     */
    public static function read($request) {
        $resource = fopen("php://input", "r");
        if($resource === false){
            throw new IOException('Impossible d\'ouverture du flux "php://input" en lecture');
        }
        $chunk_read = 0;

        $temp_file = tempnam(sys_get_temp_dir(), 'binary');
        $fp = fopen($temp_file, 'w');
        
        /* Lecture des données, $threshold à la fois */
        while ($data = fread($resource, self::THRESHOLD)) {
            $chunk_read = $chunk_read + strlen($data);

            if (($request->getMaxBodySize() !== null) && ($chunk_read > $request->getMaxBodySize())) {
                fclose($resource);
                fclose($fp);
                throw new SizeLimitExceededException('Taille du flux HTTP invalide', $request->getMaxBodySize(), $chunk_read);
            }

            fwrite($fp, $data);
        }

        /* Fermeture du flux */
        fclose($fp);
        fclose($resource);
        
        return new TempFile($temp_file);
    }

}

