<?php

namespace beaba\core\services;

use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class BatchRequest extends core\Service implements core\IRequest
{

    /**
     * @var string
     */
    protected $_location;

    /**
     * @var array
     */
    protected $_parameters;

    /**
     * Gets the requested method type
     * @see GET, POST, PUT, DELETE ...
     * @return string
     */
    public function getMethod()
    {
        return 'ENV';
    }

    /**
     * Change the current url location
     * @param string $url 
     */
    public function setLocation($url)
    {
        $this->_location = $url;
        return $this;
    }

    /**
     * Gets the requested unique ressource location
     * @see /index.html
     * @return string
     */
    public function getLocation()
    {
        if (!$this->_location) {
            if ($this->hasParameter('--help')) {
                $this->_location = '/help';
            } else {
                $this->_location = '/';
            }
        }
        return $this->_location;
    }

    /**
     * Gets the batch base dir
     * @return string
     */
    public function getBaseDir() {
        return '/';
    }
    
    /**
     * Gets the response type : html, xml, json ...
     * @return string
     */
    public function getResponseType()
    {
        return 'text';
    }

    /**
     * Gets the list of requested parameters
     * @return array
     */
    public function getParameters()
    {
        if (is_null($this->_parameters)) {
            $this->_parameters = array();
            $options = $this->_app->config->getConfig('options');
            if ( empty($_SERVER['argv']) ) $_SERVER['argv'] = array();
            if ( 
                !empty($_SERVER['argv'][0]) && 
                $_SERVER['argv'][0] == $_SERVER['SCRIPT_NAME']
            ) {
                array_shift($_SERVER['argv']);
            }
            //print_r($options);
            $reader = new ArgTokenizer($_SERVER['argv']);
            while ($arg = $reader->getNext()) {
                $this->_parameters[strtolower($arg[0])] = $arg[1];
            }
            // checking passed parameters
            foreach ($options as $name => $conf) {
                if (!isset($this->_parameters[$name])) {
                    // checking aliases
                    if (!empty($conf['alias'])) {
                        if (is_string($conf['alias'])) {
                            $conf['alias'] = array($conf['alias']);
                        }
                        foreach ($conf['alias'] as $alias) {
                            if (isset($this->_parameters[$alias])) {
                                $this->_parameters[$name] = $this->_parameters[$alias];
                                unset($this->_parameters[$alias]);
                                break;
                            }
                        }
                    }
                    if (!isset($this->_parameters[$name])) {
                        // handling required arguments
                        if (
                            !empty($conf['required'])
                        ) {
                            throw new \ErrorException(
                                'The --' . $name . ' parameter is required'
                            );
                        }
                        // asign default value
                        if (!empty($conf['default'])) {
                            $this->_parameters[$name] = $conf['default'];
                        }
                    }
                }
                // checking the parameter types
                if (
                    !empty($conf['type'])
                    && isset($this->_parameters[$name])
                ) {
                    $fail = false;
                    switch ($conf['type']) {
                        // handles an array
                        case 'array':
                            if (!is_array($this->_parameters[$name])) {
                                $this->_parameters[$name] = array(
                                    $this->_parameters[$name]
                                );
                            }
                            break;
                        // handles a flag
                        case 'flag':
                            if (
                                $this->_parameters[$name] === '1'
                                || $this->_parameters[$name] === 'true'
                                || $this->_parameters[$name] === 'y'
                                || $this->_parameters[$name] === true
                            ) {
                                $this->_parameters[$name] = true;
                            } elseif (
                                $this->_parameters[$name] === '0'
                                || $this->_parameters[$name] === 'false'
                                || $this->_parameters[$name] === 'n'
                                || $this->_parameters[$name] === false
                            ) {
                                $this->_parameters[$name] = true;
                            } else {
                                $fail = true;
                            }
                            break;
                        // check or build a list of files
                        case 'files':
                            if (!is_array($this->_parameters[$name])) {
                                $this->_parameters[$name] = array(
                                    $this->_parameters[$name]
                                );
                            }
                            $files = $this->_parameters[$name];
                            $this->_parameters[$name] = array();
                            foreach ($files as $target) {
                                $target = $this->_location(
                                    $target
                                );
                                $this->_parameters[$name][] = $target;
                            }
                            break;
                        // defines a target file or path
                        case 'target':
                            $this->_parameters[$name] = $this->_location(
                                $this->_parameters[$name]
                            );
                            break;
                        // check a file
                        case 'file':
                            $this->_parameters[$name] = $this->_location(
                                $this->_parameters[$name]
                            );
                            $fail = !is_file($this->_parameters[$name]);
                            break;
                        // check a directory
                        case 'directory':
                            $this->_parameters[$name] = $this->_location(
                                $this->_parameters[$name]
                            );
                            $fail = !is_dir($this->_parameters[$name]);
                            break;
                    }
                    // check if type fails
                    if ($fail) {
                        unset($this->_parameters[$name]);
                        if (!empty($conf['required'])) {
                            throw new \LogicException(
                                'Bad ' . $name . ' parameter : is required '
                            );
                        } else {
                            $this->_app->getLogger()->warning(
                                'Bad ' . $name . ' parameter : will be ignored '
                            );
                        }
                    }
                }
                // handling the trigger
                if (
                    !empty($this->_parameters[$name])
                    && !empty($conf['handler'])
                ) {
                    $trigger = $conf['handler'];
                    $trigger($this->_app, $this->_parameters);
                }
            }
        }
        return $this->_parameters;
    }

    /**
     * Defines a location
     * @param type $target
     * @return type 
     */
    protected function _location($target)
    {
        return str_replace(
                '${BEABA_PATH}', BEABA_PATH, $target
        );
    }

    /**
     * Gets the specified parameter
     * @return mixed
     */
    public function getParameter($name)
    {
        if (!$this->_parameters)
            $this->getParameters();
        if (isset($this->_parameters[$name])) {
            return $this->_parameters[$name];
        } else {
            return null;
        }
    }

    /**
     * Check if the specified parameter is defined
     * @return boolean
     */
    public function hasParameter($name)
    {
        if (!$this->_parameters)
            $this->getParameters();
        return isset($this->_parameters[$name]);
    }

}

/**
 * Arguments tokenizer
 */
class ArgTokenizer
{

    private $_items;
    private $_key;
    private $_value;
    private $_offset;

    /**
     * Initialize the commandline arguments tokenizer
     * @param type $items 
     */
    public function __construct($items)
    {
        $this->_items = $items;
        $this->_offset = -1;
    }

    /**
     * Gets the next token
     * @return mixed 
     */
    public function getNext()
    {
        $this->_offset += 1;
        if ($this->_offset < count($this->_items)) {
            $entry = $this->_items[$this->_offset];
            if ($entry[0] === '-') {
                $result = is_null($this->_key) ?
                    null : $this->_consumePair();
                if (strpos($entry, '=') !== false) {
                    $entry = explode('=', $entry, 2);
                    $this->_value = $entry[1];
                    $entry = $entry[0];
                }
                $this->_key = ltrim($entry, '-');
                if (!is_null($result)) {
                    return $result;
                } else {
                    return $this->getNext();
                }
            } else {
                if (!empty($this->_value)) {
                    if (!is_array($this->_value))
                        $this->_value = array($this->_value);
                    $this->_value[] = $entry;
                } else {
                    $this->_value = $entry;
                }
                return $this->getNext();
            }
        } else {
            if (!is_null($this->_key)) {
                return $this->_consumePair();
            } else {
                return false;
            }
        }
    }

    /**
     * Consume current key / value pair
     * @return array 
     */
    private function _consumePair()
    {
        if ($this->_value) {
            $result = array(
                $this->_key, $this->_value
            );
        } else {
            $result = array(
                $this->_key, true
            );
        }
        $this->_key = null;
        $this->_value = null;
        return $result;
    }

}