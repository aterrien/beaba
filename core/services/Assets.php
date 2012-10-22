<?php

namespace beaba\core\services;

use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Assets extends core\Service implements core\IAssets
{

    protected $_packages = array();
    protected $_config;

    /**
     * Check if the specified package is defined
     * @param string $package 
     * @return boolean
     */
    public function hasConfig($package)
    {
        if (!$this->_config)
            $this->_config = $this->_app->config->getConfig(
                'assets', false, true
            );
        return isset($this->_config[$package]);
    }

    /**
     * Verify and raise an exception if the specified package is not
     * defined
     * @param string $package 
     * @return void
     */
    protected function requirePackage($package)
    {
        if (!$this->hasConfig($package)) {
            throw new \OutOfRangeException(
                'Undefined asset package : ' . $package
            );
        }
    }

    /**
     * Gets the specified package configuration
     * @param string $package 
     * @return array
     * @throws Exception
     */
    public function getConfig($package)
    {
        $this->requirePackage($package);
        return $this->_config[$package];
    }

    /**
     * Attach a package to the current app
     * @param string $package 
     * @return void
     */
    public function attach($package)
    {
        $config = $this->getConfig($package);
        if (!in_array($package, $this->_packages)) {
            if (!empty($config['depends'])) {
                foreach ($config['depends'] as $dependency) {
                    $this->attach($dependency);
                }
            }
            $this->_packages[] = $package;
        }
    }

    /**
     * Remove the package usage
     * @param string $package 
     * @return void
     */
    public function detach($package)
    {
        $offset = array_search($package, $this->_packages);
        if ($offset !== false) {
            unset($this->_packages[$offset]);
        }
    }

    /**
     * Retrieves the list of css includes
     * @return array
     */
    public function getCss()
    {
        $result = array();
        foreach ($this->_packages as $package) {
            $config = $this->getConfig($package);
            if (!empty($config['css'])) {
                foreach ($config['css'] as $item) {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    /**
     * Gets a list of JS links
     * @return array
     */
    public function getJs()
    {
        $result = array();
        foreach ($this->_packages as $package) {
            $config = $this->getConfig($package);
            if (!empty($config['js'])) {
                foreach ($config['js'] as $item) {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

}
