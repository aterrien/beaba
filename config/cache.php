<?php

return array(
    'drivers' => array(
        'file'          => 'beaba\\core\\cache\\File',
        'apc'           => 'beaba\\core\\cache\\APC',
        'memcached'     => 'beaba\\core\\cache\\Memcached',
    ),
    'default'   => 'main',
    'pool'      => array(
        'main'  => array(
            'driver'    => 'file',
            'options'   => array()
        )
    )
);