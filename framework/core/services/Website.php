<?php
namespace beaba\core\services;
use \beaba\core;
/**
 * Description of Website
 * @author Tara Sidaya Chiriac <contac@self-tech.fr>
 * @license http://www.self-tech.fr/license/components.html Commercial Closed-Source
 * @copyright Copyright (c) 2011, Tara Sidaya Chiriac
 * @package core
 */
class Website  extends core\Service implements core\IWebsite {
    protected $config;
    protected $filename = 'config/website.php';
    /**
     * Check if the specified configuration key is defined
     * @param string $key
     * @return boolean 
     */
    public function hasConfig( $key ) {
        if ( !$this->config ) {
            $this->config =  get_include( $this->filename );
        }
        return isset( $this->config[ $key ] );
    }
    /**
     * Requires the specified configuration key
     * @param string $key 
     * @throws \OutOfRangeException
     */
    protected function requireConfig( $key ) {
        if ( !$this->hasConfig( $key ) ) {
            throw new \OutOfRangeException(
              'Undefined website config : ' . $key
            );            
        }
    }
    /**
     * Gets the configuration entry
     * @param string $key 
     * @return mixed
     */
    public function getConfig( $key ) {
        $this->requireConfig($key);
        return $this->config[ $key ];
    }
    /**
     * Sets the specified configuration entry
     * @param string $key
     * @param mixed $value 
     * @return void
     */
    public function setConfig( $key, $value ) {
        if ( !$this->config ) {
            $this->config =  get_include( $this->filename );
        }        
        if ( 
           is_array($value) 
           && isset($this->config[$key]) 
           && is_array($this->config[$key])
        ) {
            $this->config[ $key ] = merge_array($this->config[ $key ], $value);
        } else {
            $this->config[$key] = $value;
        }        
    }
    /**
     * Gets the website name
     * @return string
     */
    public function getName() {
        return $this->getConfig( 'name' );
    }
    /**
     * Sets the current website name
     * @param string $value 
     */
    public function setName( $value ) {
        $this->setConfig('name', $value);
    }
    /**
     * Gets the page title
     * @return string
     */
    public function getTitle() {
        return $this->getConfig( 'title' );
    }
    /**
     * Sets the current page title
     * @param string $value 
     */
    public function setTitle( $value ) {
        $this->setConfig('title', $value);
    }
    /**
     * Gets the page description
     * @return string
     */
    public function getDescription() {
        return $this->getConfig( 'description' );
    }
    /**
     * Sets the current page description
     * @param string $value 
     */
    public function setDescription( $value ) {
        $this->setConfig('description', $value);
    }    
    /**
     * Gets the page template
     * @return string
     */
    public function getTemplate() {
        return $this->getConfig( 'template' );
    }
    /**
     * Sets the current page template
     * @param string $value 
     */
    public function setTemplate( $value ) {
        $this->setConfig('template', $value);
    }    
    /**
     * Gets the page layout
     * @return string
     */
    public function getLayout() {
        return $this->getConfig( 'layout' );
    }
    /**
     * Sets the current page layout
     * @param string $value 
     */
    public function setLayout( $value ) {
        $this->setConfig('layout', $value);
    }    
}