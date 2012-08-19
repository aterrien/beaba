<?php

namespace beaba\core\storage;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class MySQL implements \beaba\core\IStorageDriver
{

    /**
     * @var array The mysql instance
     */
    protected $_config;
    
    protected $_driver;

    /**
     * Initialize the MySQL driver
     * @param array $config 
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
        // setting defaults
        if ( empty($this->_config['host'])) {
            $this->_config['host'] = 'localhost';
        }
        if ( empty($this->_config['user']) ) {
            $this->_config['user'] = 'root';
        }
        if ( empty($this->_config['password']) ) {
            $this->_config['password'] = '';
        }
        if ( empty($this->_config['port']) ) {
            $this->_config['port'] = 3306;
        }
    }

    /**
     * Gets the MySQL driver
     * @return mysqli
     */
    protected function _getDriver() {
        if ( !$this->_driver ) {
            $this->_driver = new \mysqli(
                $this->_config['host'],
                $this->_config['user'],
                $this->_config['password'],
                empty($this->_config['database']) ?
                    null : $this->_config['database'],
                $this->_config['port']
            );
        }
        return $this->_driver;
    }


    /**
     * Create a select statement
     * @return IStorageRequest
     */
    public function select( \beaba\core\IModel $target ) 
    {
        return new \beaba\core\StorageRequest(
            $this, $target
        );
    }

    /**
     * Create a select statement
     * @return IStorageRequest
     */
    public function delete( \beaba\core\IModel $target, array $primaries ) {
        if ( count($primaries) > 1 ) {
            $this->_getDriver()->query(
                'DELETE FROM %target% WHERE %id% = %value% LIMIT 1',
                array(
                    '$target' => $target->getName(),
                    '$id' => $target->getPrimary(),
                    'value' => $primaries[0]
                )
            );
        } else {
            $this->_getDriver()->query(
                'DELETE FROM %target% WHERE %id% IN (%values%)',
                array(
                    '$target' => $target->getName(),
                    '$id' => $target->getPrimary(),
                    'values[]' => $primaries
                )
            );
        }
        return $this;
    }

    /**
     * Inserts values and returns the created primary
     * @return integer
     */
    function insert( \beaba\core\IModel $target, array $values );

    /**
     * Update the specified record with specified values
     * @return IStorageRequest
     */
    function update( \beaba\core\IModel $target, array $values, $primary );
}
