<?php
namespace beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class ErrorHandler extends Service implements IErrorHandler {
    /**
     * @var ILogger
     */
    protected $logger;
    public function attach( ILogger $logger ) {
        $this->logger = $logger;       
        ini_set( 'display_errors', 1 );
        error_reporting( -1 );
        set_error_handler( array( $this, 'catchError' ) );
        set_exception_handler( array( $this, 'catchException' ) );                
    }
    public function detach() {
        set_error_handler( null );
        set_exception_handler( null );                
    }
    public function catchException( \Exception $ex ) {
        $this->logger->error( $ex->__toString() );        
    }
    public function catchError($no,$str,$file,$line) {
        $this->catchException(
            new \ErrorException($str,$no,0,$file,$line)
        );
    }    
}
