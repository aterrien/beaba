<?php
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
namespace beaba\tests\units\core;

use \mageekguy\atoum;
require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Test class wrapper
 */
class ArrayMerge extends atoum\test {
    public function testMain() 
    {
        $reader = new \beaba\core\ArrayMerge();
        $reader
            ->addData( array(
                'key1' => 'val\'ue',
                'key2' => array(
                    'val2' => 'to-be-replaced',
                    'val3' => 123
                ),
                1, 2
            ))->addData( array(
                'key2' => array(
                    'val2' => 'replaced',
                    'val4' => 'another merged value',
                ), 4, 5
            ))->addFile(__DIR__ . '/merge_sample.php');
        $this->assert()
            ->array( 
                eval(
                    'return ' . $reader->__toString() . ';'
                )
            )
            ->isNotEmpty(
                'The array should contains data'
            )
            ->hasKeys(
                array(
                    'key1', 'key2', 
                    'dyna' . $_SERVER['SCRIPT_NAME']
                ), 'Bad merge'
            )
            ->notContainsValues(
                array('val\'ue','to-be-replaced'), 
                'Bad merge'
            )
        ;
    }
}
