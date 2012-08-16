<?php

namespace beaba\core\http;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class ViewException extends \beaba\core\Exception
{

    protected $_response;
    protected $_inner;

    public function __construct($response, \Exception $inner = null)
    {
        $this->_response = $response;
        $this->_inner = $inner;
        parent::__construct(
            'Internal Action Error', 500
        );
    }

    /**
     * Gets the view response
     * @return mixed
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Gets the inner exception
     * @return Exception
     */
    public function getInnerException()
    {
        return $this->_inner;
    }

}