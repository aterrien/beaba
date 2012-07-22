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
            return 'application\\controllers\\' . strtolower($parts[1]) . '::' . strtolower($parts[2]);
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
