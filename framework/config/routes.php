<?php
return array(
    'index' => array(
        'check' => array(
            'equals', '/'
        ),
        'route' => 'application\\controllers\\index::index'
    ),
    'action' => array(
        'check' => array(
            'path', 1, 2
        ),
        'route' => function( $url ) {
            $parts = explode('/', $url);
            if ( !isset( $parts[2] ) ) $parts[2] = 'index';
            return 'application\\' . strtolower($parts[1]) . '::' . strtolower($parts[2]);
        }
    )
);
