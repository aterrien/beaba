<?php

namespace beaba\core\services;

use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class BatchResponse extends HttpResponse
{
    protected $_code;
    protected $_message;
    public function setCode($code, $message)
    {
        if ( $code > 399 ) {
            $this->_code = $code;
        } else {
            $this->_code = 0;
        }
        $this->_message = $message;
        return $this;
    }
    /**
     * Sets the response header
     * @param string $attribute
     * @param string $value
     * @return IResponse
     */
    public function setHeader($attribute, $value)
    {
        return $this;
    }
}