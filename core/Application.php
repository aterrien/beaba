<?php

namespace beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
abstract class Application extends Event
{
    /**
     * the application start
     */
    const E_LOAD = 'onLoad';
    /**
     * before the request dispatch
     */
    const E_DISPATCH = 'onDispatch';
    /**
     * if an error occurs during the dispatch
     */
    const E_ERROR = 'onError';
    /**
     * before rendering the view
     */
    const E_BEFORE_RENDER = 'beforeRender';
    /**
     * before rendering the view
     */
    const E_AFTER_RENDER = 'afterRender';
    /**
     * the application ends
     */
    const E_UNLOAD = 'onUnload';
    /**
     * @var array List of services configuration
     */
    protected $_services;

    /**
     * @var array List of services instances
     */
    protected $_instances = array();

    /**
     * The current configuration instance
     * @var Configuration
     */
    public $config;

    /**
     * Initialize the application
     */
    public function __construct(array $config = null)
    {
        // initialize the configuration layer
        $this->config = new Configuration($this, $config);
        // initialize the event handler
        parent::__construct($this);
        // attach core default services : error manager + logger
        $this->getService('errors')->attach(
            $this->getService('logger')
        );
        // raise the application start event
        $this->_raise(self::E_LOAD);
    }

    /**
     * Uninitialize the app
     */
    public function __destruct()
    {
        $this->_raise(self::E_UNLOAD);
    }

    /**
     * Gets a service instance
     * @param string $name 
     * @return IService
     */
    public function getService($name)
    {
        if (!isset($this->_instances[$name])) {
            if (!$this->_services) {
                $this->_services = $this->config->getConfig('services');
            }
            if (!isset($this->_services[$name])) {
                throw new \Exception(
                    'Undefined service : ' . $name
                );
            }
            $this->_instances[$name] = new $this->_services[$name]($this);
        }
        return $this->_instances[$name];
    }

    /**
     * Gets the specified storage driver
     * @param string $name 
     * @return IStorageDriver
     */
    public function getStorage( $name )
    {
        return $this
            ->getService('storage')
            ->getDriver( $name )
        ;
    }

    /**
     * Gets a meta model storage structure
     * @param string $name
     * @return IStorageMeta
     */
    public function getModel( $name )
    {
        return $this
            ->getService('storage')
            ->getModel( $name )
        ;
    }

    /**
     * Gets the informations layer
     * @return IInfos
     */
    public function getInfos()
    {
        return $this->getService('infos');
    }

    /**
     * Gets the plugins layer
     * @return IPluginManager
     */
    public function getPlugins()
    {
        return $this->getService('plugins');
    }

    /**
     * Gets the asset manager
     * @return IAssets
     */
    public function getAssets()
    {
        return $this->getService('assets');
    }

    /**
     * Gets the response handler
     * @return IResponse
     */
    public function getResponse()
    {
        return $this->getService('response');
    }

    /**
     * Gets the view manager
     * @return IView
     */
    public function getView()
    {
        return $this->getService('view');
    }

    /**
     * Gets the logging service
     * @return ILogger
     */
    public function getLogger()
    {
        return $this->getService('logger');
    }

    /**
     * Gets the current request
     * @return IRequest
     */
    public function getRequest()
    {
        return $this->getService('request');
    }

    /**
     * Gets a url from the specified route 
     * @param string $route
     * @param array $args 
     */
    public function getUrl( $route, array $args = null ) 
    {
        return 
            $this->getRequest()->getBaseDir() . 
            $this->getService('router')->getUrl( $route, $args )
        ;
    }

    /**
     * Execute the specified action controller 
     * @param string $controller
     * @param string $action
     * @param array $params 
     * @return string
     */
    public function execute($controller, $action, $params)
    {
        $instance = new $controller($this);
        return $instance->execute($action, $params);
    }

    /**
     * Dispatching the specified request
     * @param string $url
     * @param array $params 
     * @throws \Exception
     */
    public function dispatch($method = null, $url = null, array $params = null)
    {
        $this->_raise(
            self::E_DISPATCH,
            array(
                'request' => $url,
                'params' => $params
            )
        );
        if (!is_callable($url)) {
            // initialize parameters
            if (!is_null($url)) {
                $this->getRequest()->setLocation($url);
            } else {
                $url = $this->getRequest()->getLocation();
            }
            $route = $this->getService('router')->getRoute($url);
        } else {
            $route = $url;
        }
        if ($route === false) {
            throw new Exception('No route found', 404);
        } else {
            if (is_string($route)) {
                // execute a controller
                $parts = explode('::', $route, 2);
                if (empty($parts[1]))
                    $parts[1] = 'index';
                return $this->execute($parts[0], $parts[1], $params);
            } else {
                // use the route as a callback
                return $route($this, $params);
            }
        }
    }

}

/**
 * The service interface
 */
interface IService
{

    /**
     * Gets the current application
     * @return Application
     */
    function getApplication();
}

/**
 * Defines a storage providers pool
 */
interface IStoragePool extends IService
{

    /**
     * Gets a storage provider from its configuration key
     * @param string $name
     * @return IStorageDriver
     */
    function getDriver($name = null);

    /**
     * Creates a storage instance for the specified driver
     * @param string $driver
     * @param array $conf
     * @return IStorageDriver
     */
    function createDriver($driver, array $conf = null);

    /**
     * Gets the specified meta structure
     * @param string $name
     * @return IModel
     */
    function getModel($name);

    /**
     * Create a meta structure
     * @param string $conf
     * @return IModel
     */
    function createModel(array $conf);
}

/**
 * Defines a storage driver
 */
interface IStorageDriver extends IService
{

    /**
     * Create a select statement
     * @return IStorageRequest
     */
    function select( IModel $target );

    /**
     * Create a select statement
     * @return IStorageRequest
     */
    function delete( IModel $target, array $primaries );

    /**
     * Inserts values and returns the created primary
     * @return integer
     */
    function insert( IModel $target, array $values );

    /**
     * Update the specified record with specified values
     * @return IStorageRequest
     */
    function update( IModel $target, array $values, $primary );
}

/**
 * Defines a request structure
 */
interface IStorageRequest extends \Iterator, \Countable
{

    /**
     * The requested model
     * @return IModel
     */
    public function getModel();

    /**
     * The storage instance
     * @return IStorageDriver
     */
    public function getStorage();

    /**
     * @return IStorageRequest
     */
    function where( $operator = 'and' );

    /**
     * @return IStorageRequest
     */
    function join( $relation );

    /**
     * @return IStorageRequest
     */
    function isEquals( $field, $value );

    /**
     * @return IStorageRequest
     */
    function isLike( $field, $value );

    /**
     * @return IStorageRequest
     */
    function isNull( $field );

    /**
     * @return IStorageRequest
     */
    function isBetween( $field, $start, $end );

    /**
     * @return IStorageRequest
     */
    function not();

    /**
     * @return IStorageRequest
     */
    function sub( $operator = 'and');

    /**
     * Ascending ordering
     * @return IStorageRequest
     */
    function orderAsc( $column );

    /**
     * Descending ordering
     * @return IStorageRequest
     */
    function orderDesc( $column );

    /**
     * Limiting the recordset size
     * @return IStorageRequest
     */
    function limit( $offset, $size );

    /**
     * Check if the reader has results
     * @returns boolean		 
     */
    function hasResults();
}

interface IModel
{

    /**
     * Gets the storage name
     * @return string
     */
    function getName();

    /**
     * Gets the primary key name
     * @return string
     */
    public function getPrimary();

    /**
     * Gets the storage driver
     * @return IStorageDriver
     */
    function getStorage();

    /**
     * Create a select request
     * @return IStorageRequest
     */
    function select();

    /**
     * @return 
     */
    function create( array $data );

    /**
     * Gets the storage columns
     * 
     * Structure :
     * {
     *      column-name: {
     *          type:   integer | string | boolean | datetime
     *          size:   numeric | long
     *          + extras
     *      }
     * }
     * @return array
     */
    function getColumns();

    /**
     * Gets the columns relations
     * 
     * Structure :
     * {
     *      relation-name: {
     *          type:   identity | foreign | many
     *          target: 
     *      }
     * }
     * @return array
     */
    function getRelations();
}

/**
 * Defines a cache providers pool
 */
interface ICachePool extends IService
{

    /**
     * Gets a cache provider from its configuration key
     * @param string $name
     * @return ICacheDriver
     */
    function get($name = null);

    /**
     * Creates a cache instance for the specified driver
     * @param string $driver
     * @param array $conf
     * @return ICacheDriver
     */
    function create($driver, array $conf = null);
}

/**
 * Defines a cache diver
 */
interface ICacheDriver extends IService
{

    /**
     * Gets the value from the specified key
     * @param string $key
     * @return mixed
     */
    function getValue($key);

    /**
     * Get values from the specified keys
     * @param array $keys
     * @return array
     */
    function getValues(array $keys);

    /**
     * Sets a value attached to the specified key
     * @param string $key
     * @param mixed $value
     * @return ICache
     */
    function setValue($key, $value);

    /**
     * Set values attaches to specified indexes (keys)
     * @param array $values
     * @return ICache
     */
    function setValues($values);

    /**
     * Remove the specified key
     * @param string $key
     * @return ICache
     */
    function unsetValue($key);

    /**
     * Remove the specified keys
     * @param array $key
     * @return ICache
     */
    function unsetValues($keys);
}

/**
 * The plugin configuration instance
 */
interface IPlugin extends IService
{
    /**
     * when the plugin is enabled
     */
    const E_ENABLE = 'onEnable';
    /**
     * when the plugin is disabled
     */
    const E_DISABLE = 'onDisable';

    /**
     * Enabled the current plugin to the specified target level
     * @param string $target
     * @return IPlugin
     */
    function enable($target = 'core');

    /**
     * Disable the current plugin from the specified target
     * @param string $target
     * @return IPlugin
     */
    function disable($target = 'core');

    /**
     *  Check if the current plugin is enabled
     *  @return boolean
     */
    function isEnabled();

    /**
     * Gets the current plugin options
     * @return array
     */
    function getOptions();

    /**
     * Gets an option value
     * @param string $name 
     * @return mixed
     */
    function getOption($name);
}

/**
 * The plugin manager instance
 */
interface IPluginManager extends IService
{

    /**
     * Gets a list of all available plugins
     * @return array
     */
    function getPlugins();

    /**
     * Gets a list of enabled plugins
     * @return array
     */
    function getEnabledPlugins();

    /**
     * Gets the specified plugin
     * @param string $name
     * @return IPlugin
     * @throws \OutOfBoundsException
     */
    function getPlugin($name);

    /**
     * Check if the specified plugin is enabled or not
     * @param string $name
     * @return boolean
     */
    function isEnabled($name);
}

/**
 * Defines the requesting service
 */
interface IRequest extends IService
{

    /**
     * Gets the requested method type
     * @see GET, POST, PUT, DELETE ...
     * @return string
     */
    public function getMethod();

    /**
     * Gets the requested unique ressource location
     * @see /index.html
     * @return string
     */
    public function getLocation();

    /**
     * Gets the request base dir (to build requests)
     * @return string 
     */
    public function getBaseDir();

    /**
     * Sets the requested unique ressource location
     * @params string $url
     * @return IRequest
     */
    public function setLocation($url);

    /**
     * Gets the response type : html, xml, json ...
     * @return string
     */
    public function getResponseType();

    /**
     * Gets the list of requested parameters
     * @return array
     */
    public function getParameters();

    /**
     * Gets the specified parameter
     * @return mixed
     */
    public function getParameter($name);

    /**
     * Check if the specified parameter is defined
     * @return boolean
     */
    public function hasParameter($name);
}

/**
 * The informations layer
 */
interface IInfos extends IService
{

    /**
     * Check if the specified configuration key is defined
     * @param string $key
     * @return boolean 
     */
    public function hasConfig($key);

    /**
     * Gets the configuration entry
     * @param string $key 
     * @return mixed
     */
    public function getConfig($key);

    /**
     * Sets the specified configuration entry
     * @param string $key
     * @param mixed $value 
     * @return void
     */
    public function setConfig($key, $value);

    /**
     * Gets the application name
     * @return string
     */
    public function getName();

    /**
     * Sets the current application name
     * @param string $value 
     */
    public function setName($value);

    /**
     * Gets the page title
     * @return string
     */
    public function getTitle();

    /**
     * Sets the current page title
     * @param string $value 
     */
    public function setTitle($value);

    /**
     * Gets the page description
     * @return string
     */
    public function getDescription();

    /**
     * Sets the current page description
     * @param string $value 
     */
    public function setDescription($value);

    /**
     * Gets the page template
     * @return string
     */
    public function getTemplate();

    /**
     * Sets the current page template
     * @param string $value 
     */
    public function setTemplate($value);

    /**
     * Gets the page layout
     * @return string
     */
    public function getLayout();

    /**
     * Sets the current page layout
     * @param string $value 
     */
    public function setLayout($value);
}

/**
 * The router interface
 */
interface IRouter extends IService
{

    /**
     * Retrieves a list of routes from the configuration
     * @return array
     */
    public function getRoutes();

    /**
     * Gets the requested route
     * @param string $url
     * @return string 
     */
    public function getRoute($url);

    /**
     * Gets a url from the specified route 
     * @param string $route
     * @param array $args 
     */
    public function getUrl( $route, array $args = null );
}

/**
 * The view interface
 */
interface IView extends IService
{

    /**
     * Sets the main layout 
     * @return IView
     */
    public function setLayout($file);

    /**
     * Initialize the layout data
     * @return IView 
     */
    public function initLayout();

    /**
     * Sets the templating file
     * @return IView
     */
    public function setTemplate($file);

    /**
     * Adds the specified data to the end of the specified
     * zone (using the specified file for the rendering)
     * @return IView
     */
    public function push($zone, $file, $datasource = null);

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
    );

    /**
     * Adds the specified data to the top of the specified
     * zone (using the specified file for the rendering)
     * @return IView
     */
    public function insert($zone, $file, $datasource = null);

    /**
     * Renders the specified file
     * @return string
     */
    public function render($file, $datasource = null);

    /**
     * Renders the current template
     * @return string
     */
    public function renderTemplate();

    /**
     * Renders the current layout
     * @return string
     */
    public function renderLayout();

    /**
     * Renders the current layout
     * @return string
     */
    public function renderPlaceholder($zone);
}

/**
 * The assets manager structure
 */
interface IAssets extends IService
{

    /**
     * Check if the specified package is defined
     * @param string $package 
     * @return boolean
     */
    public function hasConfig($package);

    /**
     * Gets the specified package configuration
     * @param string $package 
     * @return array
     * @throws Exception
     */
    public function getConfig($package);

    /**
     * Attach a package to the current app
     * @param string $package 
     * @return void
     */
    public function attach($package);

    /**
     * Remove the package usage
     * @param string $package 
     * @return void
     */
    public function detach($package);

    /**
     * Retrieves the list of css includes
     * @return array
     */
    public function getCss();

    /**
     * Gets a list of JS links
     * @return array
     */
    public function getJs();
}

/**
 * Services interfaces
 */
interface IResponse extends IService
{

    /**
     * Sets the response code
     * @param string $code
     * @param string $message
     * @return IResponse
     */
    public function setCode($code, $message);

    /**
     * Sets the response header
     * @param string $attribute
     * @param string $value
     * @return IResponse
     */
    public function setHeader($attribute, $value);

    /**
     * Write a new line with the specified message
     * @param string $message
     * @return IResponse
     */
    public function writeLine($message);

    /**
     * Outputs the specified contents
     * @param string $message
     * @return IResponse
     */
    public function write($message);
}

/**
 * The logger interface
 */
interface ILogger extends IService
{
    /**
     * Logs debug infos
     */
    const DEBUG = 1; // 0001
    /**
     * Logs info 
     */
    const INFO = 2; // 0010
    /**
     * Logs warnings
     */
    const WARNING = 4; // 0100
    /**
     * Logs errors
     */
    const ERROR = 8; // 1000    
    /**
     * Gets the current logger level
     * @return int
     */

    function getLevel();

    /**
     * Sets the log level
     */
    function setLevel($level);

    /**
     * Send a debug message
     */
    function debug($message);

    /**
     * Sends an info message
     */
    function info($message);

    /**
     * Send a warning message
     */
    function warning($message);

    /**
     * Send an error message
     */
    function error($message);
}

/**
 * The error handler
 */
interface IErrorHandler
{

    /**
     * Attach an logger and starts watching errors
     */
    function attach(ILogger $logger);

    /**
     * Detach the error handler
     */
    function detach();

    /**
     * Catch the specified exception
     */
    function catchException(\Exception $ex);
}

/**
 * Inner service class (automatically loaded)
 */
class Service extends Event implements IService
{
    /**
     * on service starts
     */
    const E_LOAD = 'onLoad';

    /**
     * Initialize the service
     * @param Application $app 
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->_onStart();
    }

    /**
     * Hook for the service starting
     */
    protected function _onStart()
    {
        $this->_raise(self::E_LOAD);
    }

    /**
     * Gets the current application
     * @return Application 
     */
    final public function getApplication()
    {
        return $this->_app;
    }

}
