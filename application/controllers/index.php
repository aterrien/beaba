<?php
namespace application\controllers;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class index extends \beaba\core\Controller {
    /**
     * The index application entry
     * @param array $args 
     */
    public function index( array $args ) {
        $this->app->getService('assets')->attach('bootstrap');
        $this->app->getService('view')->setLayout('layout.html');
        $this->app->getService('view')->setTemplate('default.html');
    }
}
