<?php
return array(
    'index' => array(
        'check' => array(
            'equals', array(
                '/', '/index'
            )
        ),
        'route' => 'application\\controllers\\index::index'
    ),
    'action' => array(
        'check' => array(
            'path', 1, 2
        ),
        'route' => function( $url ) {
            $parts = explode('/', $url, 2);
            if ( !isset( $parts[2] ) ) $parts[2] = 'index';
            $target = BEABA_APP . '/controllers/' . strtolower($parts[1]) . 'php';
            $class = 'application\\controllers\\' . strtolower($parts[1]);
            if ( !class_exists( $class, false ) ) {
                if ( 
                    !file_exists( $target ) 
                ) {
                    throw new \beaba\core\Exception(
                        'Unable to find controller : ' . strtolower($parts[1]), 
                        404
                    );
                } else {
                    include $target;
                    if ( !class_exists( $class, false ) ) {
                        throw new \beaba\core\Exception(
                            'Unable to find class controller : ' . $class, 
                            404
                        );
                    }
                }                
            }
            return $class . '::' . strtolower($parts[2]);
        }
    ),
    'beaba' => array(
        'check' => array(
            'starts', '/beaba'
        ),
        'route' => function( $url ) {
            $parts = explode('/', substr($url, 6), 2);
            if ( !isset( $parts[2] ) ) $parts[2] = 'index';
            return 'beaba\\controllers\\' . strtolower($parts[1]) . '::' . strtolower($parts[2]);            
        }
    )
);
