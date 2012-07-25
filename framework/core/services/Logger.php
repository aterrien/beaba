<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Logger extends core\Service implements core\ILogger 
{
    protected $_level = 15;
    /**
     * Gets the current logger level
     * @return int
     */
    public function getLevel() {
        return $this->_level;
    }
    /**
     * Sets the log level
     */
    public function setLevel( $level ) {
        $this->_level = $level;
    }
    /**
     * Send a debug message
     */
    public function debug($message) 
    {
        if ( $this->_level | self::DEBUG ) {
            $this->_app->getService('response')->writeLine(
                'DEBUG : ' . $message
            );
        }
    }
    /**
     * Sends an info message
     */
    function info($message) 
    {
        if ( $this->_level | self::INFO ) {
            $this->_app->getService('response')->writeLine(
                'INFO : ' . $message
            );
        }                        
    }
    /**
     * Send a warning message
     */
    function warning($message) 
    {
        if ( $this->_level | self::WARNING ) {
            $this->_app->getService('response')->writeLine(
                'WARNING : ' . $message
            );
        }                
    }
    /**
     * Send an error message
     */
    function error($message) 
    {
        if ( $this->_level | self::ERROR ) {
            $this->_app->getService('response')->writeLine(
                'ERROR : ' . $message
            );
        }        
    }   
}
