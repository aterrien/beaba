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
        case 'application':
            include BEABA_APP . '/' . strtr($location[1], '\\', '/') . '.php';
            break;
        case 'plugin':
            include BEABA_PATH . '/plugins/' . strtr($location[1], '\\', '/') . '.php';
            break;
        case 'beaba':
            include BEABA_PATH . '/' . strtr($location[1], '\\', '/') . '.php';
            break;
    }
    return class_exists( $class );
});

// include the build file
if ( file_exists( BEABA_PATH . '/build.php' ) ) {
    include BEABA_PATH . '/build.php';
}

/**
 * An array merging helper
 * @params array $original
 * @params array $additionnal
 * @return array
 */
function merge_array( $original, $additionnal ) {
    foreach($additionnal as $key => $value) {
        if ( is_numeric( $key ) ) {
            $original[] = $value;
        } else {
            $original[$key] = (
                isset($original[$key]) && is_array($value) ?
                merge_array($original[$key], $additionnal[$key]) : $value
            );            
        }
    }
    return $original;    
}

/**
 * Includes a list of files and retrieves theirs merged results
 * @param string $target 
 * @return array
 */
function get_include( $target ) {
    $result = array();
    $callback = strtr($target, '/.', '__');
    if (function_exists($callback) ) {
        $result = $callback();
    }
    if ( file_exists( $target ) ) {
        $result = merge_array(include($target), $result);
    }
    if ( file_exists( BEABA_APP . '/' . $target ) ) {
        $result = merge_array(include( BEABA_APP . '/' . $target ), $result);
    }
    if ( file_exists( BEABA_PATH . '/' . $target ) ) {
        $result = merge_array(include( BEABA_PATH . '/' . $target ), $result);
    }
    return $result;
}