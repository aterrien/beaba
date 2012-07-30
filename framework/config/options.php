<?php
use \beaba\core\Application;
/**
 * @read-only true
 */
return array(
    'help'  => array(
        'title'         => 'Shows this informations screen',
        'type'          => 'flag',
        'alias'         => array('h', '?'),
        'handler'       => function( Application $app, &$params ) {
            $out = $app->getResponse();
            $options = $app->config->getConfig('options');
            $out->writeLine('Options :');
            $out->writeLine(null);
            foreach( $options as $name => $opt ) {                
                $desc = ' --' . $name;
                if ( !empty($opt['alias']) ) {
                    if ( is_string($opt['alias']) ) $opt['alias'] = array($opt['alias']);
                    if ( count($opt['alias']) == 1) {
                        $desc .= ' or -' . $opt['alias'][0];
                    } else {
                        $desc .= ' or [-' . implode(', -', $opt['alias']) . ']';
                    }
                }
                $out->writeLine($desc);
                $out->writeLine($opt['title']);
                $out->writeLine(null);
            }
            exit(0);
        }
    ),    
    'import'  => array(
        'title'         => 'Imports the specified file (json) configuration',
        'type'          => 'file',
        'alias'         => 'i', 
        'default'       => substr(
            $_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME']) - 4
         ) . '.json',
        'handler'       => function( Application $app, &$params ) {
            $params = merge_array(                    
                json_decode( 
                    file_get_contents($params['import']), true 
                ), $params
            );      
        }
    )    
);