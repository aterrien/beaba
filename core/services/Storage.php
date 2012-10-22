<?php
namespace beaba\core\services;
use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Storage extends core\Service implements core\IStoragePool
{

    /**
     * @var array List of storage drivers instances
     */
    protected $_drivers = array();

    /**
     * @var array List of meta definitions
     */
    protected $_models = array();

    /**
     * Gets a storage provider from its configuration key
     * @param string $name
     * @return IStorageDriver
     */
    public function getDriver($name = null) {
        if ( !isset( $this->_driver[$name] ) ) {
            $conf = $this->_app->config->getConfig('storage');
            if ( empty($conf['pool'][ $name ]) ) {
                throw new \OutOfBoundsException(
                    'Undefined connection instance : ' . $name
                );
            }
            $this->_drivers[ $name ] = $this->createDriver(
                $conf['pool'][$name]['driver'],
                $conf['pool'][$name]['options']
            );
        }
        return $this->_drivers[ $name ];
    }

    /**
     * Creates a storage instance for the specified driver
     * @param string $driver
     * @param array $conf
     * @return IStorageDriver
     */
    public function createDriver($driver, array $conf = null)
    {
        $conf = $this->_app->config->getConfig('storage');
        if ( empty( $conf['drivers'][$driver] ) ) {
            throw new \OutOfBoundsException(
                'Undefined driver : ' . $driver
            );
        }
        $target = $conf['drivers'][$driver];
        if ( is_string( $target ) ) {
            // should be a class name
            return new $target( $conf );
        } elseif ( is_array($target) ) {
            // should be a callback array
            return call_user_func($target, $conf);
        } else {
            // should be a closure
            return $target( $conf );
        }
    }

    /**
     * Gets the specified meta structure
     * @param string $name
     * @return IModel
     */
    public function getModel($name)
    {
        if ( !isset( $this->_models[$name] ) ) {
            $conf = $this->_app->config->getConfig('models');
            if ( empty($conf[ $name ]) ) {
                throw new \OutOfBoundsException(
                    'Undefined model definition : ' . $name
                );
            }
            $this->_models[ $name ] = $this->createModel(
                $name, $conf[$name]
            );
        }
        return $this->_models[ $name ];
    }

    /**
     * Create a meta structure
     * @param string $conf
     * @return IModel
     */
    public function createModel($name, array $conf) {
        if ( !empty($conf['class'])) {
            $class = $conf['class'];
            return new $class( $name, $this->_app, $conf );
        } else {
            return new \beaba\core\Model( $name, $this->_app, $conf );
        }
    }
}