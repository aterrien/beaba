<?php

namespace beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class Batch extends Application
{

    /**
     * Initialize the batch mode
     * @param array $config 
     */
    public function __construct(array $config = null)
    {
        parent::__construct($config);
        // the default batch header
        $this->getResponse()->writeLine(
            'Command Line Tool for : ' .
            $this->getInfos()->getName() .
            ' - by ' .
            $this->getInfos()->getConfig('author')
        );
        $this->getResponse()->writeLine(
            str_repeat('=', 70)
        );
        $this->getResponse()->writeLine(
            $this->getInfos()->getDescription()
        );
        $this->getResponse()->writeLine(
            'type --help for more infos'
        );
        $this->getResponse()->writeLine(null);
        // clean the template output
        $this->getView()->setTemplate('empty');
    }

    /**
     * Dispatching the specified request
     * @param string $method
     * @param array $params 
     */
    public function run($method = null, array $params = null)
    {
        $params = $this->_loadParameters($params);
        try {
            parent::dispatch(null, $url, $params);
            exit(0);
        } catch (\Exception $ex) {
            $this->_raise(
                self::E_ERROR, array(
                'request' => $url,
                'params' => $params,
                'error' => $ex
                )
            );
            $this->getLogger()->error("\n" . $ex->getMessage());
            $this->getLogger()->info("\n" . $ex->getFile() . ' at ' . $ex->getLine());
            $this->getResponse()->write("\n\n" . 'Program exit with a fatal error - CODE(1)' . "\n\n");
            $this->getLogger()->warning("\n" . $ex->getTraceAsString());
            exit(1);
        }
    }

}