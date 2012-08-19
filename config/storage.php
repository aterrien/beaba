<?php
return array(
    'drivers' => array(
        'mysql' => 'beaba\\core\\storage\\MySQL'
    ),
    'pool' => array(
        'default' => array(
            'driver' => 'mysql',
            'options' => array(
                'host'      => 'localhost',
                'user'      => 'root',
                'password'  => '',
                'port'      => 3306
            )
        )
    )
);