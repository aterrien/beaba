<?php

namespace beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Model implements IModel
{

    protected $_config;
    protected $_app;

    public function __construct(Application $app, $config)
    {
        $this->_config = $config;
        $this->_app = $app;
    }

    /**
     * Gets the storage name
     * @return string
     */
    public function getName()
    {
        return
            empty($this->_config['database']) ?
            $this->_config['table'] :
            $this->_config['database'] . $this->_config['table']
        ;
    }

    /**
     * Gets the storage driver
     * @return IStorageDriver
     */
    public function getStorage()
    {
        return $this->_app->getStorage(
                $this->_config['storage']
        );
    }

    /**
     * Create a select request
     * @return IStorageRequest
     */
    public function select()
    {
        return $this->getStorage()->select($this);
    }

    /**
     * Creates a new entity filled with specified data
     * @return ActiveRecord
     */
    public function create(array $data = null)
    {
        if ( empty($this->_config['entity'])) {
            return new ActiveRecord( $this, $data );
        } else {
            $class = $this->_config['entity'];
            return new $class( $this, $data );
        }
    }

    /**
     * Gets the storage columns
     * @return array
     */
    public function getColumns()
    {
        return $this->_config['columns'];
    }

    /**
     * Gets the columns relations
     * @return array
     */
    public function getRelations()
    {
        return $this->_config['relations'];
    }

}

/**
 * Defines an active record entity
 */
class ActiveRecord
{

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var IModel 
     */
    protected $_model;

    public function __construct(IModel $model, array $data = null)
    {
        $this->_data = $data ? $data : array();
        $this->_model = $model;
    }

}