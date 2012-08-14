<?php
namespace beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Configuration
{

    /**
     * @var array List of merged configuration
     */
    protected $_config = array();

    /**
     * @var array Website injected configuration
     */
    protected $_local = array();

    /**
     * @var array The core configuration
     */
    protected $_core = array();

    /**
     * @var array The application configuration
     */
    protected $_app = array();

    /**
     * Initialize a configuration layer with specified data
     * @param array $config 
     */
    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            $this->_local = $config;
        }
    }

    /**
     * Gets the merged configuration from different sources
     * @param string $key 
     * @return array
     */
    public function getConfig($key)
    {
        if (!isset($this->_config[$key])) {
            $this->_config[$key] = merge_array(
                $this->getCoreConfig($key), 
                merge_array(
                    $this->getAppConfig($key), 
                    $this->getLocalConfig($key)
                )
            );
        }
        return $this->_config[$key];
    }

    /**
     * Reads a configuration from a callback function
     * @param string $prefix
     * @param string $key
     * @return array
     */
    protected function _readCallbackConf($prefix, $key)
    {
        $callback = 'config_' . $prefix . '_' . strtr($key, '/.', '__');
        return function_exists($callback) ?
            $callback() : false
        ;
    }

    /**
     *
     * @param type $path
     * @param type $key
     * @return type 
     */
    protected function _readFileConf($path, $key)
    {
        $target = $path . '/config/' . $key . '.php';
        return file_exists($target) ?
            include( $target ) : array()
        ;
    }

    /**
     * Gets the local website configuration
     * @param string $key 
     * @return array
     */
    public function getLocalConfig($key)
    {
        if (!isset($this->_local[$key])) {
            $this->_local[$key] = (
                $data = $this->_readCallbackConf('local', $key)
            ) ? $data : $this->_readFileConf('.', $key);
        }
        return $this->_local[$key];
    }

    /**
     * Gets the core configuration
     * @param string $key
     * @return array
     */
    public function getCoreConfig($key)
    {
        if (!isset($this->_core[$key])) {
            $this->_core[$key] = (
                BEABA_BUILD_CORE && 
                $data = $this->_readCallbackConf('core', $key)
            ) ? $data : $this->_readFileConf(BEABA_PATH, $key);
        }
        return $this->_core[$key];
    }

    /**
     * Gets the application configuration
     * @param string $key
     * @return array
     */
    public function getAppConfig($key)
    {
        if (!isset($this->_app[$key])) {
            $this->_app[$key] = (
                BEABA_BUILD_APP && 
                $data = $this->_readCallbackConf('app_' . APP_NAME , $key)
            ) ? $data : $this->_readFileConf( BEABA_APP . '/' . APP_NAME , $key);
        }
        return $this->_app[$key];
    }
}