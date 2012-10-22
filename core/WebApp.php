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
    public function renderHtml($response)
    {
        $this->getResponse()->setHeader(
            'Content-Type', 'text/html'
        );
        if (is_null($response))
            $response = $this->getView();
        if ($response instanceof IView) {
            return $response->renderTemplate();
        } elseif (is_string($response)) {
            return $response;
        } elseif (
            isset($response['view'])
        ) {
            if (isset($response['template'])) {
                $this->getView()->setTemplate($response['template']);
            }
            $this->getView()->setLayout($response['view']);
            if (isset($response['placeholders'])) {
                $this->getView()->initLayout();
                foreach ($response['placeholders'] as $target => $widgets) {
                    foreach ($widgets as $id => $widget) {
                        if (is_numeric($id)) {
                            $this->getView()->push(
                                $target, empty($widget['render']) ?
                                    null : $widget['render'], isset($widget['data']) ?
                                    $widget['data'] : null
                            );
                        } else {
                            $this->getView()->attach(
                                $target, $id, empty($widget['render']) ?
                                    null : $widget['render'], isset($widget['data']) ?
                                    $widget['data'] : null
                            );
                        }
                    }
                }
            }
            return $this->getView()->renderTemplate();
        } else {
            throw new \Exception(
                'Unsupported response type', 400
            );
        }
    }

    /**
     * Serialize the result as a json
     * @param mixed $response
     * @return string 
     */
    public function renderJson($response)
    {
        $this->getResponse()->setHeader(
            'Content-Type', 'application/json'
        );
        return json_encode($response, JSON_FORCE_OBJECT);
    }

    /**
     * Serialize the result as a xml
     * @param mixed $response 
     * @return string
     */
    public function renderXml($response)
    {
        $this->getResponse()->setHeader(
            'Content-Type', 'text/xml'
        );
        if (is_string($response)) {
            return '<response><![CDATA[' . $response . ']]></response>';
        } elseif (is_array($response)) {
            $return = '<response>';
            foreach ($response as $key => $value) {
                $return .= '<' . $key . '>' . $value . '</' . $key . '>';
            }
            return $return . '</response>';
        } else {
            return '<response />';
        }
    }

    /**
     * Executes the response callbacks and returns it's result
     * @param string $response
     * @param string $method
     * @param string $format
     * @param array $args
     * @return mixed 
     */
    public function processResponse($response, $method, $format, array $args = null)
    {
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
                if (is_callable($out)) {
                    $out = $out($this, $args);
                }
                if (is_array($out) && !isset($out['view'])) {
                    // handle the response type
                    if (isset($out[$format])) {
                        $out = $out[$format];
                    } elseif (isset($out['*'])) {
                        $out = $out['*'];
                    } else {
                        throw new http\BadFormat(
                            $this, array_keys($out)
                        );
                    }
                }
                if (is_callable($out)) {
                    $response = $out($this, $args);
                } else {
                    $response = $out;
                }
            }
        }
        return $response;
    }

    /**
     * Renders the response to the specified format
     * @param mixed $response
     * @param string $format
     * @return string
     */
    public function renderResponse($response, $format)
    {
        // renders the template
        switch ($format) {
            case 'html':
                return $this->renderHtml($response);
            case 'json':
                return $this->renderJson($response);
            case 'xml':
                return $this->renderXml($response);
            case 'rss':
                return $this->renderRss($response);
            default:
                throw new http\BadFormat(
                    $this, array('html', 'json', 'xml', 'rss')
                );
        }
    }

    /**
     * Dispatching the specified request
     * @param string $url
     * @param array $params 
     */
    public function dispatch(
        $method = null, $url = null, 
        array $params = null, $format = null
    )
    {
        $params = $this->_loadParameters($params);
        if (is_null($method))
            $method = $this->getRequest()->getMethod();
        if (is_null($format))
            $format = $this->getRequest()->getResponseType();
        try {
            $response = parent::dispatch($method, $url, $params);
            $this->_raise(self::E_BEFORE_RENDER);
            
            $response = $this->renderResponse(
                $this->processResponse($response, $method, $format, $params)
                , $format
            );
        } catch (\Exception $ex) {
            // general exception catch
            if ($ex instanceof Exception && !$ex->isHttpError()) {
                // @todo decide what to show ?
                $response = null;
                $this->getResponse()->setCode(
                    $ex->getCode(), $ex->getHttpMessage()
                );
                if ($ex instanceof http\Redirect) {
                    if ($format === 'json') {
                        $this->getResponse()->setCode(
                            200, 'OK'
                        );
                        $response = $this->renderResponse(
                            array(
                            'redirect' => $ex->getUrl()
                            ), $format
                        );
                    } else {
                        $this->getResponse()->setHeader(
                            'Location', $ex->getUrl()
                        );
                    }
                }
            } else {
                $this->_raise(
                    self::E_ERROR, array(
                    'request' => $url,
                    'params' => $params,
                    'error' => $ex
                    )
                );
                if ($ex instanceof http\ViewException) {
                    $this->getService('response')->setCode(
                        $ex->getCode(), $ex->getHttpMessage()
                    );
                    if ($format === 'html') {
                        $response = $this->renderResponse(
                            $ex->getResponse(), $format
                        );
                    } else {
                        if (
                            $ex->getInnerException()
                            instanceof http\FormValidation
                        ) {
                            $ex = $ex->getInnerException();
                            $errors = array();
                            foreach ($ex->getErrors() as $field => $title) {
                                $errors[] = array(
                                    'field' => $field,
                                    'message' => $title
                                );
                            }
                            $response = $this->renderResponse(
                                array(
                                'errors' => $errors
                                ), $format
                            );
                        } else {
                            $response = $this->renderResponse(
                                array(
                                'error' => array(
                                    'title' => $ex->getMessage(),
                                    'from' => $ex->getInnerException()->getMessage()
                                )
                                ), $format
                            );
                        }
                    }
                } else {
                    $response = $this->renderResponse(
                        $this->execute(
                            'beaba\\controllers\\errors', 'show', array(
                            'request' => $url,
                            'params' => $params,
                            'error' => $ex
                            )
                        ), $format
                    );
                }
            }
        }
        // clean-up the view service
        $this->_raise(
            self::E_AFTER_RENDER, array(
            'response' => &$response
            )
        );
        // flush the view instance
        return $response;
    }

}
