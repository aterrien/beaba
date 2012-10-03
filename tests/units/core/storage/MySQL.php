<?php

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
namespace beaba\tests\units\core\storage;


/**
 * Test class wrapper
 */
class MySQL extends \beaba\tests\Unit
{
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
                        'storage' => 'default',
                        'table' => 'tests',
                        'database' => '__tests',
                        'columns' => array(
                            'when' => 'datetime',
                            'rand' => 'integer',
                            'name' => 'string:32'
                        ),
                        'relations' => array(
                            'units' => 'many:unit'
                        )
                    )
                )
            ));
        }
        return $this->_app;
    }
    
    /**
     * Testing the insertions
     */
    public function testInsert() {
        $instance = $this->getApp()->getModel('test')->create(array(
            'rand' => 123,
            'when' => time(),
            'name' => 'Test Insert'
        ))->save();
        echo $instance->getIdentifier();
    }
    
    /**
     * Testing the requests
     */
    public function testRequest() {
        $result = $this->getApp()->getModel('test')->query(
            '
                WITH
                    test.faills (
                        SELECT t FROM tests t 
                        WHERE t.result = 0
                        LIMIT 0, 3
                    )
                WHERE 
                    t.rand > %range%
                    AND (
                        t.rand < :range
                        OR t.rand = :range
                    ) AND
                    t.title like :pattern
            ',
            array(
                'range' => rand(1, 1000)
            )
        );
        // counting the result size
        echo count($result);
    }
}
