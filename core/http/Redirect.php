<?php

namespace beaba\core\http;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Redirect extends \beaba\core\Exception
{

    protected $_url;

    public function __construct($url)
    {
        $this->_url = $url;
        parent::__construct(
            'Moved Temporary', 302
        );
    }

    public function getUrl()
    {
        return $this->_url;
    }

}