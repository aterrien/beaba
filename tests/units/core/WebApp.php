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
class WebApp extends atoum\test {
    public function test__construct() 
    {
        return new \beaba\core\WebApp(array(
            'services' => array(
                'request' => 'beaba\\core\\services\\HttpRequest'
            ),
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
        $this->assert()->string(
            $this
            ->test__construct()
            ->dispatch(
                'GET', '/', null, 'html'
             )
        )->isEqualTo('<h1>Hello world</h1>');
    }
}