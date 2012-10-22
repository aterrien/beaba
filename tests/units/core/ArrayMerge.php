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
class ArrayMerge extends \beaba\tests\Unit {
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
        $result = eval(
            'return ' . $reader->__toString() . ';'
        );
        $this->assert(
            is_array(
                $result
            )
        )->assert(
            count($result) > 0
        )->assert(
            !empty($result['key1'])
            && !empty($result['key2'])
            && !empty($result['dyna' . $_SERVER['SCRIPT_NAME']])
        )->assert(
            !in_array('val\'ue', $result)
            && !in_array('to-be-replaced', $result)
        );
        $reader->addFile( __DIR__ . '/merge_extends.php' );
        $result = eval(
            'return ' . $reader->__toString() . ';'
        );
        $this->assert(
            !empty( $result['header'] )
            && !empty( $result['footer'] )
        );
    }
}
