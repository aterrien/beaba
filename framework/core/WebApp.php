<?php

namespace beaba\core;

/**
 * This file is distributed under the MIT Open Source
 * License. See README.MD for details.
 * @author Ioan CHIRIAC
 */
class WebApp extends Application
{
    /**
     * Dispatching the specified request
     * @param string $url
     * @param array $params 
     */
    public function dispatch($url = null, array $params = null)
    {
        try {
            parent::dispatch($url, $params);
        } catch (\Exception $ex) {
            $this->_raise(
                self::E_ERROR, array(
                'request' => $url,
                'params' => $params,
                'error' => $ex
                )
            );
            if ($ex instanceof Exception && !$ex->isHttpError()) {
                $this->getService('response')->setCode(
                    $ex->getCode(), $ex->getHttpMessage()
                );
            } else {
                $this->execute(
                    'beaba\\controllers\\errors', 'show', array(
                    'request' => $url,
                    'params' => $params,
                    'error' => $ex
                    )
                );
            }
        }
        $this->_raise(self::E_BEFORE_RENDER);
        $response = $this->getView()->renderTemplate();
        $this->_raise(
            self::E_AFTER_RENDER, array(
            'response' => &$response
            )
        );
        $this->getResponse()->write($response);
    }
}
