<?php
namespace beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Event 
{
    protected static $_listeners = array();
    
    /**
     * Raise the specified event over the specified object
     * @param Event $object
     * @param string $event
     * @param array $args 
     * @return array
     */
    protected static function _raiseObjectEvent( Event $object, $event, array $args = null ) 
    {
        $class = get_class( $object );
        if ( !isset( static::$_listeners[ $class ] ) ) {
            static::_loadListeners( $class );
        }
        $results = array();
        if ( !empty( static::$_listeners[ $class ][ $event ] ) ) {
            foreach( static::$_listeners[ $class ][ $event ] as $listener ) {
                if ( is_array( $listener ) ) {
                    $results[] = call_user_func_array( $listener , array( $object, $args ) );
                } else {
                    $results[] = $listener( $object, $args );
                }
            }            
        }
        return $results;
    }    
    /**
     * Loads all class listeners
     * @param string $class
     * @return void
     */
    protected static function _loadListeners( $class ) 
    {
        static::$_listeners[ $class ] = get_include( 
            'events/' . strtr($class, '\\', '/') 
        );
    }    
    /**
     * Raise an event
     * @param string $event
     * @param array $args 
     * @return array
     */
    protected function _raise( $event, array $args = null ) 
    {
        return static::_raiseObjectEvent( $this, $event, $args );
    }
}