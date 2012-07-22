<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class View extends core\Service implements core\IView {
    protected $template;
    protected $layout;
    protected $placeholders = array();
    protected $renderers = array();
    /**
     * Sets the main layout 
     */
    public function setLayout( $file ) {
        $this->layout = $file;
    }
    /**
     * Sets the templating file
     */
    public function setTemplate( $file ) {
        $this->template = $file;
    }
    /**
     * Adds the specified data to the end of the specified
     * zone (using the specified file for the rendering)
     */
    public function push( $zone, $file, $datasource ) {
        if ( !isset($this->placeholders[ $zone ]) ) {
            $this->placeholders[ $zone ] = array();
        }
        $this->placeholders[ $zone ][] = array(
            $file, $datasource
        );
    }
    /**
     * Adds the specified data to the top of the specified
     * zone (using the specified file for the rendering)
     */
    public function insert( $zone, $file, $datasource ) {
        if ( !isset($this->placeholders[ $zone ]) ) {
            $this->placeholders[ $zone ] = array();
        }
        array_unshift(
            $this->placeholders[ $zone ], 
            array( $file, $datasource )
        );
    }
    /**
     * Converts the current datasource to an array
     * @return array
     */
    protected function getDatasource( $datasource = null ) {
        if ( !$datasource || is_array($datasource) ) {
            return $datasource;
        }
        if ( is_callable($datasource) ) {
            return $datasource( $this->app );
        }
    }
    /**
     * Renders the specified file
     * @return string
     */
    public function render( $file, $datasource = null ) {
        // already buffered
        if ( isset($this->renderers[ $file ]) ) {
            ob_start();
            $this->renderers[ $file ]( 
                $this->app, $this->getDatasource($datasource)
            );
            return ob_get_clean();
        }
        // check for a callback
        $callback = strtr($file, '/.', '__');
        if ( function_exists($callback) ) {
            $this->renderers[ $file ] = $callback;
            ob_start();
            $this->renderers[ $file ]( 
                $this->app, $this->getDatasource($datasource)
            );
            return ob_get_clean();
        }
        // check for a file include
        $target = 'views/' . $file;
        $app = $this->app;
        $data = $this->getDatasource($datasource);
        if ( !file_exists( $target ) ) {
            if ( file_exists( BEABA_APP . '/' . $target ) ) {
                $target =  BEABA_APP . '/' . $target;
            } elseif ( file_exists( BEABA_PATH . '/' . $target ) ) {
                $target = BEABA_PATH . '/' . $target;
            } else {
                $this->getApplication()->getLogger()->warning(
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
    public function renderTemplate() {
        if ( $this->template ) {
            return $this->render( $this->template );
        } else {
            return $this->render( 
                $this->app->getWebsite()->getTemplate() 
            );
        }        
    }
    /**
     * Renders the current layout
     * @return string
     */
    public function renderLayout() {
        if ( $this->layout ) {
            return $this->render( $this->layout );
        } else {
            return $this->render( 
                $this->app->getWebsite()->getLayout() 
            );
        }        
    }
    /**
     * Renders the current layout
     * @return string
     */
    public function renderPlaceholder( $zone ) {
        if ( isset( $this->placeholders[$zone]) ) {
            $result = '';
            foreach( $this->placeholders[$zone] as $item ) {
                $result .= $this->render( $item[0], $item[1] );
            }
            return $result;
        } else {
            $this->getApplication()->getLogger()->warning(
              'Undefined placeholder : ' . $zone
            );
            return '';
        }
    }
}