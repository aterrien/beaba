<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class ErrorHandler extends core\Service implements core\IErrorHandler 
{
    /**
     * @var ILogger
     */
    protected $_logger;
    /**
     * Close the error handler
     * @return void
     */
    public function __destruct() {
        $this->detach();
    }    
    /**
     * Attach a logger to the error handler
     * @param core\ILogger $logger 
     * @return ErrorHandler
     */
    public function attach( core\ILogger $logger ) 
    {
        $this->_logger = $logger;
        ErrorManager::getInstance()->attach($this);
        return $this;
    }
    /**
     * Stop listening errors
     * @return ErrorHandler
     */
    public function detach() 
    {
        ErrorManager::getInstance()->detach($this);
        return $this;
    }
    /**
     * Raise an exception (redirect to logger output)
     * @param \Exception $ex
     * @return ErrorHandler 
     */
    public function raise( \Exception $ex ) 
    {
        $this->_logger->error( $ex->__toString() );
        return $this;
    }

}

class ErrorManager {
    /**
     * @var \SplObjectStorage Contains all error listeners
     */
    protected $_handlers;
    
    /**
     * @var ErrorManager
     */
    protected static $_instance;
    
    /**
     * Initialize the error manager
     */
    private function __construct() {
        $this->_handlers = new \SplObjectStorage();
        ini_set( 'display_errors', 1 );
        error_reporting( -1 );
        set_error_handler( array( $this, 'catchError' ) );
        set_exception_handler( array( $this, 'catchException' ) );
    }
    /**
     * Disable the errors catching
     */
    public function __destruct() {
        restore_error_handler();
        restore_exception_handler();
    }
    
    /**
     * Gets the error manager instance
     * @return ErrorManager
     */
    public static function getInstance() {
        if ( !static::$_instance ) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
    
    /**
     * Attach a new error handler
     * @param ErrorHandler $handler
     * @return ErrorManager 
     */
    public function attach( ErrorHandler $handler ) {
        $this->_handlers->attach($handler);
        return $this;
    }
    /**
     * Detach the specified error handle
     * @param ErrorHandler $handler
     * @return ErrorManager 
     */
    public function detach( ErrorHandler $handler ) {
        $this->_handlers->detach($handler);
        return $this;
    }
    /**
     * Catching an application exception
     * @param \Exception $ex
     * @return boolean 
     */
    public function catchException( \Exception $ex ) 
    {
        foreach( $this->_handlers as $handler ) {
            $handler->raise( $ex );
        }
        return true;
    }
    /**
     * Catching a PHP internal error
     * @param integer $no
     * @param string $str
     * @param string $file
     * @param integer $line
     * @return boolean 
     */
    public function catchError($no,$str,$file,$line) 
    {
        $this->catchException(
            new \ErrorException($str,$no,0,$file,$line)
        );
        return true;
    }
}