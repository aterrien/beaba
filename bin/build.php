#!/usr/bin/env php
<?php
// DEFINES APPLICATION PATH
defined('BEABA_PATH') OR define(
    'BEABA_PATH',
    !empty($_SERVER['BEABA_PATH']) ?
    $_SERVER['BEABA_PATH'] :
    '../framework'
);
require_once BEABA_PATH . '/bootstrap.php';
// CONFIGURE THE SCRIPT
$app = new beaba\core\Batch(
    array(
        'infos'      => array(
            'name'          => 'beabaBuilder',
            'title'         => 'Beaba Builder Script',
            'description'   => 'Use this script to build beaba applications and improve theirs'."\n"
                               . 'performances with an OPCACHE engine',
            'author'        => 'I.CHIRIAC'
        ),
        'options'    => array(
            'target'  => array(
                'title'         => 'The building target file',
                'type'          => 'target',
                'alias'         => 't',
                'required'      => true
            ),
            'files'   => array(
                'title'         => 'List of files to build',
                'type'          => 'files',
                'alias'         => 'f',
                'required'      => true
            ),
            'config'   => array(
                'title'         => 'List of configuration to build',
                'type'          => 'files',
                'alias'         => 'c',
                'required'      => true
            ),            
            'format'  => array(
                'title'         => 'Format the php code',
                'type'          => 'flag',
                'default'       => true
            ),
            'comments' => array(
                'title'         => 'Removes comments',
                'type'          => 'flag',
                'default'       => 'true'
            )
        )
    )
);
// RUN THE SCRIPT
$app->dispatch(function( $app, $args ) {
    echo ' ... run into :)';
    print_r( $args );
    exit(0);
});