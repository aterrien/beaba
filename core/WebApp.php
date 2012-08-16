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
     * Renders the HTML response
     * @param mixed $response
     * @return string 
     */
    public function renderHtml( $response ) 
    {
        $this->getResponse()->setHeader(
            'Content-Type', 'text/html'
        );
        if ( is_null($response) ) $response = $this->getView();
        if ($response instanceof IView) {
            return $response->renderTemplate();
        } elseif (is_string($response)) {
            return $response;
        } elseif ( 
            isset( $response['view'] )
        ) {
            if ( isset( $response['template'] ) ) {
                $this->getView()->setTemplate($response['template']);
            }
            if ( isset( $response['placeholders'] ) ) {
                foreach($response['placeholders'] as $target => $widget) {
                    $this->getView()->push(
                        $target, 
                        $widget['render'],
                        isset($widget['data']) ? $widget['data'] : null
                    );
                }
            }
            return $this
                ->getView()
                ->setLayout($response['view'])
                ->renderTemplate()
            ;
        } else {
            throw new Exception(
                'Unsupported response type', 400
            );
        }
    }
    
    /**
     * Serialize the result as a json
     * @param mixed $response
     * @return string 
     */
    public function renderJson( $response ) 
    {
        $this->getResponse()->setHeader(
            'Content-Type', 'application/json'
        );
        return json_encode($response, JSON_FORCE_OBJECT);
    }
    
    /**
     * Dispatching the specified request
     * @param string $url
     * @param array $params 
     */
    public function dispatch(
        $method = null, $url = null, array $params = null, $format = null
    )
    {
        if (is_null($method))
            $method = $this->getRequest()->getMethod();
        if (is_null($format))
            $format = $this->getRequest()->getResponseType();
        try {
            $response = parent::dispatch($method, $url, $params);
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
                $response = $this->execute(
                    'beaba\\controllers\\errors', 'show', array(
                    'request' => $url,
                    'params' => $params,
                    'error' => $ex
                    )
                );
            }
        }
        $this->_raise(self::E_BEFORE_RENDER);
        // EXECUTING THE RESPONSE
        if (!is_string($response)) {
            if (is_array($response)) {
                // execute the rest method
                if (isset($response[$method])) {
                    $out = $response[$method];
                } elseif (isset($response['*'])) {
                    $out = $response['*'];
                } else {
                    throw new http\BadMethod(
                        $this, array_keys($response)
                    );
                }
                // handle a callback
                if ( is_callable( $out ) ) {
                    $out = $out();
                }
                // handle the response type
                if ( isset( $out[ $format ] ) ) {
                    $out = $out[ $format ];
                } elseif (isset($out['*'])) {
                    $out = $out['*'];
                } else {
                    throw new http\BadFormat(
                        $this, array_keys($out)
                    );
                }
                if ( is_callable( $out ) ) {
                    $response = $out();
                } else {
                    $response = $out;
                }
            }
            // renders the template
            switch( $format ) {
                case 'html':
                    $response = $this->renderHtml($response);
                    break;
                case 'json':
                    $response = $this->renderJson($response);
                    break;
                case 'xml':
                    $response = $this->renderXml($response);
                    break;
                case 'rss':
                    $response = $this->renderRss($response);
                    break;
                default:
                    throw new http\BadFormat(
                        $this, array('html', 'json', 'xml', 'rss')
                    );
            }
        }
        // clean-up the view service
        $this->_raise(
            self::E_AFTER_RENDER, array(
                'response' => &$response
            )
        );
        // flush the view instance
        unset($this->_services['view']);
        return $response;
    }
}
