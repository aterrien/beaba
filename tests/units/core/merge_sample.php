<?php
return array(
    'key1' => array(
        'hello'
    ), 6, 7,
    'key2' => array(
        'val3' => 'inherit ...',
        'val5' => function() {
            return true;
        }
    ),
    'dyna' . $_SERVER['SCRIPT_NAME'] => strtoupper('result')
);