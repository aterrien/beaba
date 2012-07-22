<?php
// DEFINES APPLICATION PATH
defined('BEABA_PATH') OR define(
    'BEABA_PATH',
    !empty($_SERVER['BEABA_PATH']) ?
    $_SERVER['BEABA_PATH'] :
    '../framework'
);
defined('BEABA_APP') OR define(
    'BEABA_APP',
    !empty($_SERVER['BEABA_APP']) ?
    $_SERVER['BEABA_APP'] :
    '../application'
);

// LOADS SYSTEM
require_once BEABA_PATH . '/bootstrap.php';

// LOADS AN APPLICATION INSTANCE
$app = new beaba\core\Application(array(
    'index' => array(
        'callback' => function( $app, $args ) {
            $app->getView()
                ->setTemplate('empty.phtml')
                ->push(
                    'content',
                    function( $app, $data ) {
                        echo 'Hello world';
                    }
                )
            ;    
        }
    )
));
$app->dispatch(
	$_SERVER['REQUEST_URI'],
	$_REQUEST
);
