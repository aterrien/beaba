<?php

namespace beaba\core\storage;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class MySQL extends \beaba\core\StorageDriver
{

    /**
     * @var array The mysql instance
     */
    protected $_config;

    /**
     * @var mysqli The SQL client instance
     */
    protected $_driver;

    /**
     * Initialize the MySQL driver
     * @param array $config 
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
        // setting defaults
        if (empty($this->_config['host'])) {
            $this->_config['host'] = 'localhost';
        }
        if (empty($this->_config['user'])) {
            $this->_config['user'] = 'root';
        }
        if (empty($this->_config['password'])) {
            $this->_config['password'] = '';
        }
        if (empty($this->_config['port'])) {
            $this->_config['port'] = 3306;
        }
    }

    /**
     * Gets the MySQL driver
     * @return mysqli
     */
    protected function _getDriver()
    {
        if (!$this->_driver) {
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
     * Handling the exception raising from the driver
     * @return void
     * @throws StorageException
     */
    protected function _raiseDriverError()
    {
        throw new \beaba\core\StorageException(
            'Storage Error ' . $this->_getDriver()->sqlstate . ' : '
            . $this->_getDriver()->error
        );
    }

    /**
     * Escaping a sql entity name
     * @param string $name
     * @return string 
     */
    protected function _escapeEntity($name)
    {
        if (strpos($name, '.') !== false ) {
            return 
                '`' . 
                str_replace(
                    '.', '`.`', 
                    strtr($name, '`\\', '-_')
                ) 
                . '`'
            ;
        } else {
            return '`' . strtr($name, '`\\', '-_') . '`';
        }
    }

    /**
     * Escaping a list of entities names
     * @param array $names 
     * @return array
     */
    protected function _escapeEntities($names)
    {
        $result = array();
        foreach ($names as $name) {
            $result[] = $this->_escapeEntity($name);
        }
        return $result;
    }

    /**
     * Escape the specified value
     * @param string $value
     * @return string 
     */
    protected function _escapeValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_string($value)) {
            return '\''.$this->_getDriver()->escape_string($value).'\'';
        } else {
            throw new \BadMethodCallException(
                'Could not serialize to SQL the specified value'
            );
        }
    }

    /**
     * Escape the specified array of values
     * @param array $values
     * @return array
     */
    protected function _escapeValues(array $values)
    {
        $result = array();
        foreach ($values as $value) {
            $result[] = $this->_escapeValue($value);
        }
        return $result;
    }

    /**
     * Executes a SQL statement : UPDATE / INSERT / DELETE
     * @param string $statement
     * @return MySQL
     * @throws StorageException
     */
    public function execute($statement)
    {
        $result = $this->_getDriver()->query($statement);
        if ($result === false)
            $this->_raiseDriverError();
        return $this;
    }

    /**
     * Executes the specified sql statement and fetch the result
     * @param array $statement
     */
    public function query($statement)
    {
        $result = $this->_getDriver()->query($statement);
        if ($result === false)
            $this->_raiseDriverError();
        return new MySQLStatement(
                $result
        );
    }

    /**
     * @inheritdoc
     */
    public function delete(\beaba\core\IModel $target, array $primaries)
    {
        $size = count($primaries);
        $result = true;
        if ($size === 1) {
            $this->execute(
                sprintf(
                    'DELETE FROM %1$s WHERE %2$s = %3$s LIMIT 1',
                    $this->_escapeEntity($target->getName()),
                    $this->_escapeEntity($target->getPrimary()),
                    $this->_escapeValue($primaries[0])
                )
            );
        } elseif ($size > 1) {
            $this->execute(
                sprintf(
                    'DELETE FROM %1$s WHERE %2$s IN (%3$s)',
                    $this->_escapeEntity($target->getName()),
                    $this->_escapeEntity($target->getPrimary()),
                    implode(',', $this->_escapeValues($primaries))
                )
            );
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    function insert(\beaba\core\IModel $target, array $values)
    {
        $this->execute(
            sprintf(
                'INSERT INTO %1$s (%2$s) VALUES (%3$s)',
                $this->_escapeEntity($target->getName()),
                implode(',', $this->_escapeEntities(array_keys($values))),
                implode(',', $this->_escapeValues($values))
            )
        );
        return $this->_getDriver()->insert_id;
    }

    /**
     * @inheritdoc
     */
    function update(\beaba\core\IModel $target, array $values, $primary)
    {
        $pairs = array();
        foreach ($values as $column => $value) {
            $pairs[] =
                $this->_escapeEntity($column)
                . '='
                . $this->_escapeValue($value)
            ;
        }
        $this->execute(
            sprintf(
                'UPDATE %1$s SET %2$s WHERE %3$s = %4$s LIMIT 1',
                $this->_escapeEntity($target->getName()), implode(',', $pairs),
                $this->_escapeEntity($target->getPrimary()),
                $this->_escapeValue($primary)
            )
        );
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function deploy(\beaba\core\IModel $target) {
        $name = $target->getName();
        if ( strpos($name, '.') !== false ) {
            // ATTEMPT TO CREATE THE DATABASE
            $dbName = explode('.', $name, 2);
            $this->execute(
                sprintf(
                    'CREATE DATABASE IF NOT EXISTS %1$s',
                    $this->_escapeEntity($dbName[0])
                )
            );
        }
        $options = array(
            'ENGINE=InnoDB',
            'DEFAULT CHARSET=utf8'
        );
        $primary = $this->_escapeEntity($target->getPrimary());
        $fields = array(
            $primary
            . ' INT(10) UNSIGNED NOT NULL AUTO_INCREMENT'
        );
        foreach($target->getColumns() as $column => $config ) {
            $fields[] = 
                $this->_escapeEntity( 
                    $this->getFieldName($column, $config)
                ) 
                . ' ' 
                . $this->getFieldDef( $config )
            ;
        }
        $fks = array();
        foreach($target->getRelations() as $column => $config ) {
            $config = $this->getRelationOptions($config);
            if ( strtolower($config['type']) === 'foreign' ) {
                $fName = $this->_escapeEntity( 
                    $this->getFieldName($column, $config)
                );
                $fks[] = $fName;
                $fields[] = $fName . ' INT(10) UNSIGNED NOT NULL';
            }
        }
        $fields[] = 'PRIMARY KEY ('.$primary.')';
        foreach($fks as $id => $fName) {
            $fields[] = 'KEY `fk' . $id . '` (' . $fName . ')';
        }
        $this->execute(
            sprintf(
                'CREATE TABLE %1$s ( %2$s ) %3$s',
                $this->_escapeEntity($name),
                implode(',', $fields),
                implode(' ', $options)
            )
        );
        
        return $this;
    }

    /**
     * Gets a field name
     * @param string|string $name
     * @param array $options
     * @return string 
     */
    protected function getFieldName( $name, $options ) {
        if ( 
            is_string($options) 
            || empty($options['name'])
        ) {
            return $name;
        } else {
            return $options['name'];
        }
    }
    
    /**
     * Gets a field options
     * @param array|string $options
     * @return array
     */
    protected function getRelationOptions( $options ) {
        if ( is_string($options) ) {
            $def = explode(':', $options);
            $options = array(
                'type' => $def[0],
                'model' => empty($def[1]) ? null: $def[1]
            );
        }
        return $options;
    }
    
    /**
     * Gets a field definition
     * @param array|string $options 
     * @return string
     */
    protected function getFieldDef( $options ) {
        if ( is_string($options) ) {
            $def = explode(':', $options);
            $options = array(
                'type' => $def[0],
                'size' => empty($def[1]) ? null: $def[1]
            );
        }
        switch( strtolower($options['type']) ) {
            case 'string':
            case 'varchar':
            case 'char':
                if ( empty($options['size']) ) $options['size'] = '255';
                return 'VARCHAR(' . $options['size'] . ')';
            case 'datetime':
            case 'timestamp':
            case 'time':
            case 'date':
                return 'TIMESTAMP';
            case 'boolean':
                return 'TINYINT UNSIGNED';
            case 'integer':
            case 'int':
            case 'numeric':
                return 'INT';
            case 'float':
                return 'FLOAT';
            case 'double':
                return 'DOUBLE';
            default:
                throw new \Exception(
                    'Unhandled type'
                );
        }
    }
    /**
     * @inheritdoc
     */
    public function destroy(\beaba\core\IModel $target) {
        $this->execute(
            sprintf(
                'DROP TABLE IF EXISTS %1$s',
                $this->_escapeEntity($target->getName())
            )
        );
        return $this;
    }
}

/**
 * Defines the mysql resultset
 */
class MySQLStatement implements \beaba\core\IStorageStatement
{

    /**
     * @var \mysqli_result
     */
    protected $_result;

    /**
     * Initialize a mysql resultset
     * @param \mysqli_result $result 
     */
    public function __construct(\mysqli_result $result)
    {
        $this->_result = $result;
    }

    /**
     * @return array
     */
    public function next()
    {
        return $this->_result->fetch_assoc();
    }

}