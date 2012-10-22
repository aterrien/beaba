<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Cache extends core\Service implements core\ICachePool 
{
    protected $_config;
    
    protected $_default;
    
    protected $_instances = array();
    
    /**
     * Gets an handler configuration
     * @param string $name
     * @return array
     * @throws \OutOfBoundsException
     */
    protected function _getConfig( $name ) 
    {
        if ( !$this->_config ) 
            $this->_config = $this->_app->config->getConfig('cache');
        if ( !empty($this->_config['pool'][$name])) {
            return $this->_config['pool'][$name];
        } else {
            throw new \OutOfBoundsException(
                'Undefined cache handler : ' . $name
            );
        }
    }

    /**
     * Gets the default handler
     * @return string
     */
    protected function _getDefaultName() 
    {
        if ( !$this->_default ) {
            if ( !$this->_config ) 
                $this->_config = $this->_app->config->getConfig('cache');
            $this->_default = $this->_config['default'];
        }
        return $this->_default;
    }

    /**
     * Gets a cache provider from its configuration key
     * @param string $name
     * @return ICacheDriver
     */
    public function get( $name = null )
    {
        if ( !$name ) $name = $this->_getDefaultName();
        
        if ( !isset( $this->_instances[$name] ) ) {
            $config = $this->_getConfig($name);
            $this->_instances[ $name ] = $this->create(
                $config['driver'], $config['options']
            );
        }
        return $this->_instances[ $name ];
    }
    /**
     * Creates a cache instance for the specified driver
     * @param string $driver
     * @param array $conf
     * @return ICacheDriver
     */
    public function create( $driver, array $conf = null ) {
        if ( !$this->_config ) 
            $this->_config = $this->_app->config->getConfig('cache');
        if ( empty( $this->_config['divers'][$driver] ) ) {
            throw new \OutOfBoundsException(
                'Undefined cache driver : ' . $driver
            );
        }
        $driver = $this->_config['divers'][$driver];
        return new $driver( $conf );
    }
}
