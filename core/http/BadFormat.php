<?php
namespace beaba\core\http;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class BadFormat extends \beaba\core\Exception
{
    /**
     * Initialize the error with a list of allowed formats
     * @param \beaba\core\Application $app
     * @param array $allow 
     */
    public function __construct( 
        \beaba\core\Application $app, array $allow = null 
    ) {
        if ( !$allow ) $allow = array('GET');
        $app->getResponse()->setHeader(
            'Allow', implode(',', $allow)
        );
        parent::__construct(
            'Allows only : '
            . implode(',', $allow)
            , 406
        );
    }
}
