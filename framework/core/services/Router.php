<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Router extends core\Service implements core\IRouter {
    /**
     * List of routing configuration
     * @var array
     */
    protected $routes;          
    /**
     * Retrieves a list of routes from the configuration
     * @return array
     */
    public function getRoutes() {
        if ( !$this->routes ) {
            $this->routes = get_include('config/routes.php');
        }
        return $this->routes;
    }       
    
    /**
     * Gets the requested route
     * @param string $url
     * @return string 
     */
    public function getRoute( $url ) {
        foreach( $this->getRoutes() as $route ) {
            if ( $this->isMatch($url, $route['check']) ) {
                if ( is_string($route['route']) ) {
                    return $route['route'];
                } else {
                    $route = $route['route']( $url );
                    if ( $route !== false ) {
                        return $route;
                    }
                }
            }
        }
        return false;
    }
    /**
     * Check if the specified route match or not
     * @param string $url
     * @param mixed $check 
     * @return boolean
     */
    protected function isMatch( $url, $check ) {
        return true;
    }
}
