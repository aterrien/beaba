<?php
namespace beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Controller {
    protected $app;
    /**
     * Initialize a new controller with the specified app
     * @param Application $app 
     */
    public function __construct( Application $app ) {
        $this->app = $app;
    }
    /**
     * Executes the specified action
     * @param string $action
     * @param array $params 
     */
    public function execute( $action, $params ) {        
        if ( !is_callable( array( $this, $action ) ) ) {
            throw new Exception(
                'Undefined action : ' . $action, 501
            );
        }
        $result = $this->$action( $params );
        return $result;        
    }
}
