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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getPrimary()
    {
        return
            empty($this->_config['primary']) ?
            'id' : $this->_config['primary']
        ;
    }

    /**
     * @inheritdoc
     */
    public function getStorage()
    {
        return $this->_app->getStorage(
                $this->_config['storage']
        );
    }

    /**
     * @inheritdoc
     */
    public function query($statement, array $parameters = null)
    {
        return $this->getStorage()->select(
                $this, $statement, $parameters
        );
    }

    /**
     * @inheritdoc
     */
    public function create(array $data = null)
    {
        if (empty($this->_config['entity'])) {
            return new ActiveRecord($this, $data);
        } else {
            $class = $this->_config['entity'];
            return new $class($this, $data);
        }
    }

    /**
     * @inheritdoc
     */
    public function getColumns()
    {
        return $this->_config['columns'];
    }

    /**
     * @inheritdoc
     */
    public function hasColumn($name)
    {
        return isset($this->_config['columns'][$name]);
    }

    /**
     * @inheritdoc
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

    /**
     * Initialize a new record
     * @param IModel $model
     * @param array $data 
     */
    public function __construct(IModel $model, array $data = null)
    {
        $this->_data = $data ? $data : array();
        $this->_model = $model;
    }

    /**
     * Gets the specified column value
     * @param string $column
     * @return mixed
     */
    public function __get($column)
    {
        if (!$this->_model->hasColumn($column)) {
            throw new \OutOfBoundsException(
                'Undefined column name : ' . $column
            );
        }
        return $this->_data[$column];
    }

    /**
     * Sets the specified column value
     * @param string $column
     * @param mixed $value 
     * @return ActiveRecord
     */
    public function __set($column, $value)
    {
        if (!$this->_model->hasColumn($column)) {
            throw new \OutOfBoundsException(
                'Undefined column name : ' . $column
            );
        }
        $this->_data[$column] = $value;
        return $this;
    }

    /**
     * Calls a magic getter / setter for a column
     * @param string $function
     * @param array $args
     * @return mixed
     */
    public function __call($function, $args)
    {
        $method = substr($function, 0, 3);
        if ($method === 'get') {
            return $this->__get(strtolower(substr($function, 3)));
        } elseif ($method === 'set') {
            return $this->__set(
                    strtolower(substr($function, 3)), $args[0]
            );
        } else {
            throw new \BadMethodCallException(
                'Undefined method ' . $function
            );
        }
    }

    /**
     * Save the current record to the database
     * @return ActiveRecord 
     */
    public function save()
    {
        $this->_model->getStorage()->insert(
            $this->_model, $this->_data
        );
        return $this;
    }

}
