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
        $this->_app->getView()->setLayout('simple.phtml');
        if ( $args['error'] instanceof core\Exception ) {
            $code = $args['error']->getCode();
            $title = $args['error']->getHttpMessage();
        } else {
            $code = 500;
            $title = 'Internal Error';
        }
        $this->_app->getService('response')->setCode(
            $code, $title
        );
        $this->_app->getWebsite()->setTitle( $code . ' - ' . $title );
        if ( $code === 404 ) {
            $this->_app->getView()->push(
                'content', 'errors/not-found.phtml', $args['error']
            );            
        } else {
            $this->_app->getView()->push(
                'content', 'errors/internal.phtml', $args['error']
            );            
        }
    }
}
