<?php
return array(
    \beaba\core\Application::E_LOAD => array(
        function( Application $sender, $args ) {
            define('APP_START', microtime(true));
        }        
    ),
    \beaba\core\Application::E_AFTER_RENDER => array(
        function( \beaba\core\Application $sender, $args ) {
            $body = strpos( $args['response'], '</body>');
            if ( $body !== false ) {
                // inserting logs
                $args['response'] = 
                    substr($args['response'], 0, $body)
                    . '<div class="logs">'
                    . 'Page duration : ' . round(microtime(true) - APP_START, 4) . ' sec'
                    . '</div>' . "\n"
                    . substr($args['response'], $body)
                ;
            }
        }
    )
);