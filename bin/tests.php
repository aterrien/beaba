#!/usr/bin/env php
<?php
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */

// DEFINES APPLICATION PATH
defined('BEABA_PATH') OR define(
        'BEABA_PATH', !empty($_SERVER['BEABA_PATH']) ?
            $_SERVER['BEABA_PATH'] :
            '..'
);
require_once BEABA_PATH . '/bootstrap.php';
require_once BEABA_PATH . '/tests/lib/testify/testify.class.php';
// CONFIGURE THE SCRIPT
$app = new beaba\core\Batch(
    array(
        'infos' => array(
            'name' => 'beabaTester',
            'title' => 'Beaba Tests Script',
            'description' => 'Use this script to test beaba applications',
            'author' => 'I.CHIRIAC'
        ),
        'options' => array(
            'classes' => array(
                'title'     => 'List of files to be tested',
                'type'      => 'array',
                'alias'     => 'c',
                'required'  => true
            ),
            'plugins' => array(
                'title' => 'Include plugins tests',
                'type' => 'flag',
                'alias' => 'p',
                'default' => false
            ),
        )
    )
);

// RUN THE SCRIPT
$app->dispatch(
    function( beaba\core\Batch $app, $args ) {
        foreach( $args['classes'] as $class ) {
            // init test
            $tests = new Testify($class);
            $instance = new $class($tests);
            // find functions to be tested
            $lookup = new ReflectionClass($instance);
            $methods = $lookup->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach($methods as $method) {
                if ( substr($method->getName(), 0, 4) === 'test') {
                    $tests->test(
                        $method->getName(). '()',
                        array( $instance, $method->getName() )
                    );
                }
            }
            // run the test
            $tests->run();
        }
    }
);
