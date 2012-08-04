<?php
/**
 * @read-only true
 */
return array(
    'router'    => 'beaba\\core\\services\\Router',
    'response'  => 'beaba\\core\\services\\Response',
    'request'   => PHP_SAPI === 'cli' ? 
        'beaba\\core\\services\\BatchRequest' :
        'beaba\\core\\services\\HttpRequest'
    ,
    'errors'    => 'beaba\\core\\services\\ErrorHandler',
    'logger'    => 'beaba\\core\\services\\Logger',
    'assets'    => 'beaba\\core\\services\\Assets',
    'view'      => 'beaba\\core\\services\\View',
    'infos'     => 'beaba\\core\\services\\Infos',
    'plugins'   => 'beaba\\core\\services\\PluginManager'
);