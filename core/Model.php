<?php

namespace beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Model implements IModel
{
    protected $_conf;
    protected $_app;
    
    public function __construct( Application $app, $conf ) {
        $this->_conf = $conf;
        $this->_app = $app;
    }
}
