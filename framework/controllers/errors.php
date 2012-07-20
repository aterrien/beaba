<?php
namespace beaba\controllers;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class errors extends \beaba\core\Controller {
    public function show( $args ) {
        if ( $args['error'] instanceof core\Exception ) {
            $this->app->getService('response')->setCode(
                $args['error']->getCode(), $args['error']->getHttpMessage()
            );
        } else {
            $this->app->getService('response')->setCode(
                500, 'Internal Error'
            );
        }
        echo '<pre>';
        print_r( $args );
    }
}
