<?php
namespace beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Controller 
{

    const GET       = 'GET';
    const POST      = 'POST';
    const DELETE    = 'DELETE';
    const PUT       = 'PUT';
    const ALL       = '*';
    const HTML      = 'html';
    const JSON      = 'json';
    const XML       = 'xml';
    const RSS       = 'rss';
    
    protected $_app;

    /**
     * Initialize a new controller with the specified app
     * @param Application $app 
     */
    public function __construct( Application $app ) 
    {
        $this->_app = $app;
    }

    /**
     * Executes the specified action
     * @param string $action
     * @param array $params 
     */
    public function execute( $action, $params ) 
    {
        if ( !is_callable( array( $this, $action ) ) ) {
            throw new Exception(
                'Undefined action : ' . $action, 501
            );
        }
        $result = $this->$action( $params );
        return $result;
    }
}
