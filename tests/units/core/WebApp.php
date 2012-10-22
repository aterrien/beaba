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
class WebApp extends \beaba\tests\Unit {
    public function test__construct() 
    {
        return new \beaba\core\WebApp(array(
            'routes' => array(
                // start routes injections
                'index' => array(
                    'callback' => function( $app, $args ) {
                        return $app->getView()
                            ->setTemplate('empty')
                            ->push(
                                'content',
                                function( $app, $data ) {
                                    echo '<h1>Hello world</h1>';
                                }
                            )
                        ;
                    }
                )
                // end of routes injection
            )
        ));
    }
    
    public function testDispatch() {
        $this->assertEqual(
            $this
                ->test__construct()
                ->dispatch(
                    'GET', '/', null, 'html'
                )
            ,
            '<h1>Hello world</h1>'
        );
    }
}