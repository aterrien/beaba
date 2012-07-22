<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Response extends core\Service implements core\IResponse {    
    public function setCode( $code, $message ) {
        header('HTTP/1.0 '.$code.' '.$message);
        header('Status: '.$code.' '.$message);
    }
    public function writeLine( $message ) {
        echo $message . '<br />';
    }
    public function write( $message ) {
        echo $message;
    }
}