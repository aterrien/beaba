<?php

namespace beaba\core\http;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class FormValidation extends \beaba\core\Exception
{

    /**
     * @var array List of errors
     */
    protected $_errors;

    protected $_form;

    /**
     * Initialize a list of form errors
     * @param array $errors 
     */
    public function __construct(array $errors, $form = null)
    {
        $this->_errors = $errors;
        $this->_form = $form;
        parent::__construct(
            'The form can\'t be processed', 500
        );
    }

    /**
     * Gets the list of errors
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /*
     * Check if the specified field contains an error
     * @return boolean
     */

    public function hasError($field, $form = null)
    {
        if ( !is_null($form) && !is_null($this->form) && $this->_form !== $form ) return false;
        return!empty($this->_errors[$field]);
    }

    /**
     * Gets the error message from the specified field
     * @param string $field 
     * @return string
     * @throws OutOfBoundsException If the field doesn't contains errors
     */
    public function getError($field, $form = null)
    {
        if ( 
            !is_null($form) 
            && !is_null($this->form) 
            && $this->_form !== $form 
        ) {
            throw new \OutOfBoundsException(
                'Undefined field error : ' . $form . '.' . $field . ' !'
            );
        }
        if ( isset( $this->_errors[$field] ) ) {
            return $this->_errors[$field];
        } else {
            throw new \OutOfBoundsException(
                'Undefined field error : ' . $field . ' !'
            );
        }
    }

}
