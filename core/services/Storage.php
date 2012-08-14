<?php
namespace beaba\core\services;
use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Storaget extends core\Service implements core\IStoragePool
{

    /**
     * @var array List of storage drivers instances
     */
    protected $_drivers = array();

    /**
     * @var array List of meta definitions
     */
    protected $_metas = array();

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
        if ( !isset( $conf['drivers'][$driver] ) ) {
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
     * @return IStorageMeta
     */
    public function getMeta($name)
    {
        if ( !isset( $this->_metas[$name] ) ) {
            $conf = $this->_app->config->getConfig('metas');
            if ( empty($conf[ $name ]) ) {
                throw new \OutOfBoundsException(
                    'Undefined meta definition : ' . $name
                );
            }
            $this->_metas[ $name ] = $this->createMeta(
                $conf[$name]
            );
        }
        return $this->_metas[ $name ];
    }

    /**
     * Create a meta structure
     * @param string $conf
     * @return IStorageMeta
     */
    public function createMeta(array $conf) {
        
    }
}
