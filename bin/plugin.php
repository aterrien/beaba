#!/usr/bin/env php
<?php
// DEFINES APPLICATION PATH
defined('BEABA_PATH') OR define(
        'BEABA_PATH', !empty($_SERVER['BEABA_PATH']) ?
            $_SERVER['BEABA_PATH'] :
            '..'
);
require_once BEABA_PATH . '/bootstrap.php';
// CONFIGURE THE SCRIPT
$app = new beaba\core\Batch(
    array(
        'infos' => array(
            'name' => 'beabaPluginManager',
            'title' => 'Beaba Plugins Manager Script',
            'description' => 'Use this script to manage beaba plugins ',
            'author' => 'I.CHIRIAC'
        ),
        'options' => array(
            'list' => array(
                'title'     => 'List available plugins',
                'handler'   => function( beaba\core\Application $app, &$params ) {
                    $app->getResponse()->writeLine('List of plugins : ');
                    foreach( $app->getPlugins()->getPlugins() as $name => $plugin ) {
                        /* @var $plugin IPlugin */
                        $app->getResponse()->writeLine(
                            $name . ' : ' . (
                                $plugin->isEnabled() ?
                                'enabled' : 'disabled'
                            )
                        );
                    }
                    exit(0);
                }
            ),
            'install' => array(
                'title'     => 'Installs a new plugin',
                'handler'   => function( beaba\core\Application $app, &$params ) {
                    if ( empty($params['install']) ) {
                        throw new \LogicException(
                            'Expected the plugin name to be installed'
                        );
                    }
                    $package = new \beaba\core\Composer(
                        $params['install']
                    );
                    
                    print_r( $params );
                    throw new \BadFunctionCallException('Not implemented');
                }
            ),
            'update' => array(
                'title'     => 'Check for plugin updates',
                'handler'   => function( beaba\core\Application $app, &$params ) {
                    throw new \BadFunctionCallException('Not implemented');
                }
            ),
            'enable' => array(
                'title'     => 'Enables the specified plugin',
                'handler'   => function( beaba\core\Application $app, &$params ) {
                    throw new \BadFunctionCallException('Not implemented');
                }
            ),
            'disable' => array(
                'title'     => 'Disables the specified plugin',
                'handler'   => function( beaba\core\Application $app, &$params ) {
                    throw new \BadFunctionCallException('Not implemented');
                }
            )
        )
    )
);
// STARTS THE BOOTSTRAP
$app->dispatch(function( beaba\core\Batch $app, $args ) {
    throw new \Exception('Expected a command : --list, --install, --update, ...');
});