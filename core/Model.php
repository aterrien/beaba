<?php
namespace beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */

/**
 * The model representation class (with default implementation)
 */
class Model implements IModel
{

    protected $_config;
    protected $_app;
    protected $_identifier;
    protected $_instances;
    protected $_lookup = array();
    
    /**
     * Initialize a new model instance
     * @param string $identifier
     * @param Application $app
     * @param array $config 
     */
    final public function __construct($identifier, Application $app, array $config)
    {
        $this->_config = $config;
        $this->_app = $app;
        $this->_identifier = $identifier;
    }

    /**
     * Gets the model identifier (from application layer)
     * @return string
     */
    final public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * @inheritdoc
     */
    final public function getApplication()
    {
        return $this->_app;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return
            empty($this->_config['database']) ?
            $this->_config['table'] :
            $this->_config['database'] . '.' . $this->_config['table']
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
            empty($this->_config['storage']) ?
            'default' : $this->_config['storage']
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
        return 
            isset($this->_config['columns'][$name])
            || isset($this->_config['relations'][$name])
            || $name === $this->getPrimary();
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
class ActiveRecord extends Event
{

    /**
     * The active record is synchronized with the database
     */
    const STATE_NONE = 0;
    /**
     * The record is new and should be inserted
     */
    const STATE_INSERT = 1;
    /**
     * The record should be synchronized with the database
     */
    const STATE_UPDATE = 2;
    /**
     * Before saving the record
     */
    const E_BEFORE_SAVE = 'onBeforeSave';
    /**
     * Before inserting the record
     */
    const E_BEFORE_INSERT = 'onBeforeInsert';
    /**
     * Before updating the record
     */
    const E_BEFORE_UPDATE = 'onBeforeUpdate';
    /**
     * Before deleting the record
     */
    const E_BEFORE_DELETE = 'onBeforeDelete';
    /**
     * After saving the record
     */
    const E_AFTER_SAVE = 'onAfterSave';
    /**
     * After inserting the record
     */
    const E_AFTER_INSERT = 'onAfterInsert';
    /**
     * After updating the record
     */
    const E_AFTER_UPDATE = 'onAfterUpdate';
    /**
     * After deleting the record
     */
    const E_AFTER_DELETE = 'onAfterDelete';
    /**
     * @var array
     */
    protected $_data;

    /**
     * @var IModel 
     */
    protected $_model;

    /**
     * @var integer The active record state
     * @see STATE_NONE, STATE_INSERT, STATE_UPDATE
     */
    protected $_state;
    /**
     * Initialize a new record
     * @param IModel $model
     * @param array $data 
     */
    final public function __construct(
        IModel $model, array $data = null, $state = self::STATE_INSERT
    ) {
        $this->_data = $data ? $data : array();
        $this->_model = $model;
        $this->_state = $state;
        parent::__construct( $this->_model->getApplication() );
    }

    /**
     * Gets the active record primary key value
     * @return numeric
     */
    public function getId() {
        if ( isset( $this->_data[ $this->_model->getPrimary() ] ) ) {
            return $this->_data[ $this->_model->getPrimary() ];
        } else {
            return null;
        }
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
        if ( $this->_state === self::STATE_NONE ) {
            $this->_state = self::STATE_UPDATE;
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
        // ignore saving 
        if ( 
            $this->_state === self::STATE_NONE 
        ) return $this;
        $this->_raise( self::E_BEFORE_SAVE );
        if ( $this->_state === self::STATE_INSERT ) {
            $this->_raise(self::E_BEFORE_INSERT);
            $id = $this->_model->getStorage()->insert(
                $this->_model, $this->_data
            );
            if ( $id !== false ) {
                $this->_data[ $this->_model->getPrimary() ] = $id;
                $this->_state = self::STATE_NONE;
                $this->_raise(self::E_AFTER_INSERT);
            }
        } elseif ( $this->_state === self::STATE_UPDATE ) {
            // handle update
            $this->_raise(self::E_BEFORE_UPDATE);
            $this->_model->getStorage()->update(
                $this->_model, $this->_data, $this->getId()
            );
            $this->_state = self::STATE_NONE;
            $this->_raise(self::E_AFTER_UPDATE);
        }
        $this->_raise(self::E_AFTER_SAVE);
        return $this;
    }
    
    /**
     * Deleting the current entity
     * @return ActiveRecord
     */
    public function delete() {
        if ( $this->_state === self::STATE_INSERT ) {
            return $this;
        }
        $this->_raise(self::E_BEFORE_DELETE);
        // process to deletion
        $this->_model->getStorage()->delete(
            $this->_model, array($this->getId())
        );
        // becomes a new entity (never created in the database)
        $this->_state = self::STATE_INSERT;
        $this->_raise(self::E_AFTER_DELETE);
        return $this;
    }

}
