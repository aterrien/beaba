<?php

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
namespace beaba\tests\units\core;

/**
 * Test class wrapper
 */
class Model extends \beaba\tests\Unit {
    private $_app;
    
    /**
     * Creates a model enabled app
     * @return WebApp
     */
    private function getApp() {
        if ( !$this->_app ) {
            $this->_app = new \beaba\core\WebApp(array(
                'models' => array(
                    'test' => array(
                        'class' => __NAMESPACE__ . '\TestMapper',
                        'entity' => __NAMESPACE__ . '\TestEntity',
                        'storage' => 'default',
                        'database' => '__tests',
                        'table' => 'tests',
                        'columns' => array(
                            'when' => 'datetime',
                            'rand' => 'integer',
                            'name' => 'string:32'
                        ),
                        'relations' => array(
                            'units' => 'many:unit'
                        )
                    ),
                    'unit' => array(
                        'database' => '__tests',
                        'table' => 'units',
                        'columns' => array(
                            'name' => 'string:10'
                        ),
                        'relations' => array(
                            'test' => 'foreign:test'
                        )
                    )
                )
            ));
        }
        return $this->_app;
    }
    /**
     * Test the model creation
     */
    public function testModel() {
        $test = $this->getApp()->getModel('test');
        $this->assertEqual(
            $test->getName(), 
            '__tests.tests'
        );
        $this->assertEqual(
            get_class($test),
            __NAMESPACE__ . '\TestMapper'
        );
    }
    
    /**
     * Creating an entry
     */
    public function testEntity() {
        $entry = $this->getApp()->getModel('test')->create(array(
            'rand' => rand(0, 1000),
            'name' => 'John'
        ));
        // test the class helper
        $this->assertEqual(
            get_class($entry),
            __NAMESPACE__ . '\TestEntity'
        );
        // test setter
        $entry->name = 'John123';
        // test getter
        $this->assertEqual(
            $entry->name,
            'John123'
        );
        // test setter
        $entry->setName('John321');
        // test getter
        $this->assertEqual(
            $entry->getName(),
            'John321'
        );
    }
    
    /**
     * Testing the save mechanism
     */
    public function testSave() {
        $record = $this->getApp()->getModel('test')->create(array(
            'rand' => rand(0, 1000),
            'name' => 'John'
        ));
        $this->assert(
            $record->save()->getId() > 0
        );
    }
}

/**
 * A fake model class
 */
class TestMapper extends \beaba\core\Model {
    
}
/**
 * A fake entity class
 */
class TestEntity extends \beaba\core\ActiveRecord {
    
}