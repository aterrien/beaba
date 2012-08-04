<?php

namespace beaba\core\services;

use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Plugin extends core\Service implements core\IPlugin
{

    protected $_enabled;
    protected $_config;

    public function __construct(core\Application $app, $enabled, array $config = null)
    {
        $this->_enabled = $enabled;
        $this->_config = $config;
        parent::__construct($app);
    }

    /**
     * Enabled the current plugin to the specified target level
     * @param string $target
     * @return IPlugin
     */
    public function enable($target = 'core'){
        $this->_enabled = true;
        $this->_raise( self::E_ENABLE );
        // @todo : write conf
    }

    /**
     * Disable the current plugin from the specified target
     * @param string $target
     * @return IPlugin
     */
    function disable($target = 'core')
    {
        $this->_enabled = false;
        $this->_raise( self::E_DISABLE );
        // @todo : write conf
    }

    /**
     *  Check if the current plugin is enabled
     *  @return boolean
     */
    function isEnabled()
    {
        return $this->_enabled;
    }

    /**
     * Gets the current plugin options
     * @return array
     */
    public function getOptions()
    {
        return $this->_config;
    }

    /**
     * Gets an option value
     * @param string $name 
     * @return mixed
     */
    public function getOption( $name ) 
    {
        return $his->_config[ $name ];
    }
}
