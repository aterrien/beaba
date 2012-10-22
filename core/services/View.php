<?php

namespace beaba\core\services;

use \beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class View extends core\Service implements core\IView
{

    protected $_defaults;
    protected $_template;
    protected $_layout;
    protected $_placeholders = array();
    protected $_renderers = array();
    protected $_flagInit = false;

    /**
     * Handle the assets loading
     */
    protected function _onStart()
    {
        parent::_onStart();
        foreach ($this->_app->getInfos()->getConfig('assets') as $asset) {
            $this->_app->getAssets()->attach($asset);
        }
    }

    /**
     * Sets the main layout 
     */
    public function setLayout($file)
    {
        $this->_flagInit = false;
        $this->_layout = $file;
        return $this;
    }

    /**
     * Sets the templating file
     */
    public function setTemplate($file)
    {
        $this->_flagInit = false;
        $this->_template = $file;
        return $this;
    }

    /**
     * Adds the specified data to the end of the specified
     * zone (using the specified file for the rendering)
     */
    public function push($zone, $file, $datasource = null)
    {
        if (!isset($this->_placeholders[$zone])) {
            $this->_placeholders[$zone] = array();
        }
        $this->_placeholders[$zone][] = array(
            $file, $datasource
        );
        return $this;
    }

    /**
     * Adds the specified data to the top of the specified
     * zone (using the specified file for the rendering)
     */
    public function insert($zone, $file, $datasource = null)
    {
        if (!isset($this->_placeholders[$zone])) {
            $this->_placeholders[$zone] = array();
        }
        array_unshift(
            $this->_placeholders[$zone], array($file, $datasource)
        );
        return $this;
    }

    /**
     * Converts the current datasource to an array
     * @return array
     */
    protected function getDatasource($datasource = null)
    {
        if (!$datasource || is_array($datasource)) {
            return $datasource;
        }
        if (is_callable($datasource)) {
            return $datasource($this->_app);
        } else {
            return $datasource;
        }
    }

    /**
     * Renders the specified file
     * @return string
     */
    public function render($file, $datasource = null)
    {
        // check for a callback
        if (is_callable($file)) {
            $key = spl_object_hash($file);
            $this->_renderers[$key] = $file;
            $file = $key;
        }
        if (!isset($this->_renderers[$file])) {
            $callback = strtr($file, '/.', '__');
            if (function_exists($callback)) {
                $this->_renderers[$file] = $callback;
            }
        }
        // already buffered
        if (isset($this->_renderers[$file])) {
            ob_start();
            $this->_renderers[$file](
                $this->_app, $this->getDatasource($datasource)
            );
            return ob_get_clean();
        }
        // check for a file include
        $app = $this->_app;
        $data = $this->getDatasource($datasource);
        if (!file_exists($target = 'views/' . $file . '.phtml')) {
            if (
                !file_exists(
                    $target = BEABA_APP . '/' 
                            . APP_NAME . '/views/' 
                            . $file . '.phtml'
                )
                && !file_exists(
                    $target = BEABA_PATH . '/views/' 
                            . $file . '.phtml'
                )
            ) {
                if ( 
                    isset( $this->_defaults[ $file ] ) 
                    && is_callable( $this->_defaults[ $file ] )
                ) 
                {
                    $this->_renderers[$file] = $this->_defaults[ $file ];
                    ob_start();
                    $this->_renderers[$file](
                        $this->_app, 
                        $data
                    );
                    return ob_get_clean();
                } else {
                    $this->_app->getLogger()->warning(
                        'Unable to locate the view : ' . $file
                    );
                }
                return '';
            }
        }
        ob_start();
        include $target;
        return ob_get_clean();
    }

    /**
     * Renders the current template
     * @return string
     */
    public function renderTemplate()
    {
        if ($this->_template) {
            return $this->render($this->_template);
        } else {
            return $this->render(
                    $this->_app->getInfos()->getTemplate()
            );
        }
    }

    /**
     * Attaching a widget data
     * @param string $zone
     * @param string $widget
     * @param string|callback $render
     * @param array|callback $datasource
     * @return IView
     */
    public function attach(
        $zone, $widget, $render = null, $datasource = null
    )
    {
        if (!isset($this->_placeholders[$zone])) {
            $this->_placeholders[$zone] = array();
        }
        if ( !isset($this->_placeholders[$zone][$widget]) ) {
            $this->_placeholders[$zone][$widget] = array(
                $render, $datasource
            );
        } else {
            if ( !is_null($render) ) {
                $this->_placeholders[$zone][$widget][0] = $render;
            }
            if ( !is_null($datasource) ) {
                if ( 
                    is_array($datasource) && 
                    is_array($this->_placeholders[$zone][$widget][1])
                ) {
                    $this->_placeholders[$zone][$widget][1] = merge_array(
                        $this->_placeholders[$zone][$widget][1],
                        $datasource
                    );
                } else {
                    $this->_placeholders[$zone][$widget][1] = $datasource;
                }
            }
        }
        return $this;
    }
    
    /**
     * Initialize the layout data
     * @return IView 
     */
    public function initLayout() {
        if ($this->_flagInit) return $this;
        $this->_flagInit = true;
        if (!$this->_layout)
            $this->_layout = $this->_app->getInfos()->getLayout();
        // load the layout default configuration
        $this->_defaults = merge_array(
            $this->_app->config->getConfig('layouts'), 
            $this->_app->config->getConfig('layouts/' . $this->_layout)
        );
        foreach ($this->_defaults as $zone => $widgets) {
            if ( is_array($widgets) ) {
                foreach ($widgets as $id => $widget) {
                    if (
                        !isset($widget['visible'])
                        || $widget['visible'] !== false
                    ) {
                        if ( is_numeric($id) ) {
                            $this->push(
                                $zone, $widget['render'], 
                                empty($widget['data']) ? 
                                array() : $widget['data']
                            );
                        } else {
                            $this->attach(
                                $zone, $id, 
                                $widget['render'], 
                                empty($widget['data']) ? 
                                array() : $widget['data']
                            );
                        }
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * Renders the current layout
     * @return string
     */
    public function renderLayout()
    {
        return $this->initLayout()->render($this->_layout);
    }

    /**
     * Renders the current layout
     * @return string
     */
    public function renderPlaceholder($zone)
    {
        if (isset($this->_placeholders[$zone])) {
            $result = '';
            foreach ($this->_placeholders[$zone] as $item) {
                $result .= $this->render($item[0], $item[1]);
            }
            return $result;
        } else {
            $this->_app->getLogger()->warning(
                'Undefined placeholder : ' . $zone
            );
            return '';
        }
    }

}