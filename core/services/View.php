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

    protected $_template;
    protected $_layout;
    protected $_placeholders = array();
    protected $_renderers = array();
    protected $_flagRender = false;

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
        $this->_layout = $file;
        return $this;
    }

    /**
     * Sets the templating file
     */
    public function setTemplate($file)
    {
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
        $target = 'views/' . $file . '.phtml';
        $app = $this->_app;
        $data = $this->getDatasource($datasource);
        if (!file_exists($target)) {
            $file = BEABA_APP . '/' . APP_NAME . '/' . $target;
            if (file_exists($file)) {
                $target = $file;
            } elseif (file_exists($file = BEABA_PATH . '/' . $target)) {
                $target = $file;
            } else {
                $this->_app->getLogger()->warning(
                    'Unable to locate the view : ' . $target
                );
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
     * Renders the current layout
     * @return string
     * @throws \LogicException
     */
    public function renderLayout()
    {
        if ($this->_flagRender) {
            throw new \LogicException(
                'The current layout was already rendered'
            );
        }
        $this->_flagRender = true;
        if (!$this->_layout)
            $this->_layout = $this->_app->getInfos()->getLayout();
        // load the layout default configuration
        $config = merge_array(
            $this->_app->config->getConfig('layouts'), $this->_app->config->getConfig('layouts/' . $this->_layout)
        );
        foreach ($config as $zone => $widgets) {
            foreach ($widgets as $widget) {
                if (
                    empty($widget['visible'])
                    || $widget['visible'] !== false
                ) {
                    $this->push(
                        $zone, $widget['render'], empty($widget['data']) ? array() : $widget['data']
                    );
                }
            }
        }
        // renders the layout
        return $this->render($this->_layout);
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