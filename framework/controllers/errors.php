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
        $this->app->getView()->setLayout('simple.phtml');
        if ( $args['error'] instanceof core\Exception ) {
            $this->app->getService('response')->setCode(
                $args['error']->getCode(), $args['error']->getHttpMessage()
            );
        } else {
            $this->app->getService('response')->setCode(
                500, 'Internal Error'
            );
        }
        if ( $args['error']->getCode() === 404 ) {
            $this->app->getView()->push(
                'content', 'errors/not-found.phtml', $args['error']
            );            
        } else {
            $this->app->getView()->push(
                'content', 'errors/internal.phtml', $args['error']
            );            
        }
    }
}
