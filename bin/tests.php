#!/usr/bin/env php
<?php
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */

// INCLUDE ATOUM BOOTSTRAP
if ( file_exists(  __DIR__ . '/../mageekguy.atoum.phar' ) ) {
    require_once __DIR__ . '/../mageekguy.atoum.phar';
} else {
    if (getenv('PHPBIN') === false) {
        set_include_path( $_SERVER['Path'] );
        putenv('PHPBIN=' . stream_resolve_include_path( 'php.exe' ) );
    }
    require_once __DIR__ . '/../vendor/mageekguy/atoum/scripts/runner.php';
}

// INCLUDE BEABA BOOTSTRAP
require_once __DIR__ . '/../bootstrap.php';

// DEFINE TESTS
//class_exists('beaba\tests\units\core\ArrayMerge');
//class_exists('beaba\tests\units\core\WebApp');
//class_exists('beaba\tests\units\core\Model');
class_exists('beaba\tests\units\core\storage\MySQL');
