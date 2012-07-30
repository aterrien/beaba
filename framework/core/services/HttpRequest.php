<?php
namespace beaba\core\services;
use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class HttpRequest
{
    /**
     * @var string
     */
    protected $_location;
    
    /**
     * Gets the requested method type
     * @see GET, POST, PUT, DELETE ...
     * @return string
     */
    public function getMethod()
    {
        
    }
    
    /**
     * Change the current url location
     * @param string $url 
     */
    public function setLocation( $url ) 
    {
        $this->_location = $url;
        return $this;
    }
    
    /**
     * Gets the requested unique ressource location
     * @see /index.html
     * @return string
     */
    public function getLocation()
    {
        if ( !$this->_location ) {
            $base_dir = substr(
                $_SERVER['SCRIPT_NAME'], 0, 
                strrpos($_SERVER['SCRIPT_NAME'], '/')
            );
            $query = strpos($_SERVER['REQUEST_URI'], '?');
            $this->_location = substr(
                $_SERVER['REQUEST_URI'], strlen($base_dir), 
                $query !== false ? 
                    $query - strlen($base_dir) : strlen($_SERVER['REQUEST_URI'])
            );        
        }
        return $this->_location;
    }

    /**
     * Gets the response type : html, xml, json ...
     * @return string
     */
    public function getResponseType()
    {
        
    }

    /**
     * Gets the list of requested parameters
     * @return array
     */
    public function getParameters()
    {
        return $_REQUEST;
    }

    /**
     * Gets the specified parameter
     * @return mixed
     */
    public function getParameter($name)
    {
        if (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        } else {
            return null;
        }
    }

    /**
     * Check if the specified parameter is defined
     * @return boolean
     */
    public function hasParameter($name)
    {
        return isset($_REQUEST[$name]);
    }

}
