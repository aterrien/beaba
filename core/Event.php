<?php
namespace beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Event
{    
    /**
     * The current application instance
     * @var Application
     */
    protected $_app;

    /**
     * The current application instance
     * @param Application $app 
     */
    public function __construct(Application $app)
    {
        $this->_app = $app;
    }
    
    /**
     * Raise an event
     * @param string $event
     * @param array $args 
     * @return array
     */
    protected function _raise($event, array $args = null)
    {
        $class = get_class($this);
        $results = array();
        $events = $this->_app->config->getConfig(
            'events/' . strtr($class, '\\', '/'), false, true
        );
        if ( !empty($events[ $event ]) ) {
            foreach( 
                $events[ $event ] as $listener 
            ) {
                if (is_array($listener)) {
                    $results[] = call_user_func_array($listener, array($this, $args));
                } else {
                    $results[] = $listener($this, $args);
                }            
            }            
        }
        return $results;
    }

}