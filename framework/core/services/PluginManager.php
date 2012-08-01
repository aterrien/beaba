<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class PluginManager extends core\Service implements core\IPluginManager 
{
    
    /**
     * @var array List of all plugins
     */
    protected $_plugins;
    
    /**
     * @var array List of enabled plugins
     */
    protected $_enabled;
    /**
     * Gets a list of all available plugins
     * @return array
     */
    public function getPlugins() {
        if ( is_null($this->_plugins) ) {
            $this->_plugins = array();
            $plugins = $this->_app->config->getConfig('plugins');
            foreach( $plugins as $name => $conf ) {
                $this->_plugins[ $name ] = new Plugin(
                    $this->_app, 
                    !empty($conf['enabled']) ? $conf['enabled'] : false,
                    !empty($conf['options']) ? $conf['options'] : array()
                );
            }
        }
        return $this->_plugins;
    }
    
    /**
     * Gets a list of enabled plugins
     * @return array
     */
    public function getEnabledPlugins() {
        if ( is_null($this->_enabled) ) {
            $this->_enabled = array();
            foreach( $this->getPlugins() as $name => $plugin ) {
                if ( $plugin->isEnabled() ) {
                    $this->_enabled[ $name ] = $plugin;
                }
            }
        }
        return $this->_enabled;
    }
    
    /**
     * Gets the specified plugin
     * @param string $name
     * @return IPlugin
     * @throws \OutOfBoundsException
     */
    public function getPlugin( $name ) {
        if ( is_null($this->_plugins) ) $this->getPlugins();
        if (isset($this->_plugins[ $name ])) {
            return $this->_plugins[ $name ];
        } else {
            throw new \OutOfBoundsException(
                'Undefined plugin : ' . $name
            );
        }
    }
    
    /**
     * Check if the specified plugin is enabled or not
     * @param string $name
     * @return boolean
     */
    public function isEnabled( $name ) {
        if ( is_null($this->_plugins) ) $this->getPlugins();
        return isset($this->_plugins[ $name ]) && $this->_plugins[ $name ]->isEnabled();
    }
}