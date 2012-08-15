<?php
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
/**
 * Autoload handler
 */
spl_autoload_register(function($class) {
    $location = explode('\\', $class, 2);
    switch( $location[0] ) {
        case 'local':
            include './' . strtr($location[1], '\\', '/') . '.php';
            break;
        case 'application':
            include 
                BEABA_APP . '/' .APP_NAME . '/' 
                . strtr($location[1], '\\', '/') . '.php';
            break;
        case 'plugin':
            include 
                BEABA_PATH . '/plugins/' 
                . strtr($location[1], '\\', '/') . '.php';
            break;
        case 'beaba':
            include BEABA_PATH . '/' . strtr($location[1], '\\', '/') . '.php';
            break;
    }
    return class_exists( $class );
});

// sets default defines
defined('BEABA_PATH') OR define('BEABA_PATH', __DIR__);
defined('BEABA_APP') OR define('BEABA_APP', BEABA_PATH . '/../applications');
defined('APP_NAME') OR define('APP_NAME', 'default');

// include the core build file
if ( file_exists( BEABA_PATH . '/build.php' ) ) {
    include BEABA_PATH . '/build.php';
    defined('BEABA_BUILD_CORE') OR define('BEABA_BUILD_CORE', true);
} else {
    define('BEABA_BUILD_CORE', false);
}

// include the application build file
if ( defined('BEABA_APP') && file_exists( BEABA_APP . '/build.php' ) ) {
    include BEABA_APP . '/build.php';
    defined('BEABA_BUILD_APP') OR define('BEABA_BUILD_APP', true);
} else {
    define('BEABA_BUILD_APP', false);
}

/**
 * An array merging helper
 * @params array $original
 * @params array $additionnal
 * @return array
 */
function merge_array( $original, $additionnal ) 
{
    if ( empty($additionnal) ) return $original;
    foreach($additionnal as $key => $value) {
        if ( is_numeric( $key ) ) {
            $original[] = $value;
        } else {
            $original[$key] = (
                !empty($original[$key]) 
                && is_array($value) 
                && is_array($original[$key]) ?
                merge_array($original[$key], $additionnal[$key]) : $value
            );
        }
    }
    return $original;
}