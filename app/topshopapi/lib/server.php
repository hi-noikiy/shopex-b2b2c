<?php

class topshopapi_server {

    /**
     * 是否需要验证AccessToken
     */
    public $isCheckAccessToken = false;

    public function process()
    {
        $params = input::get();
        //API日志记录开始
        $this->__apiLogStart();
        try
        {
            $version = input::get('api_ver');
            if( !$version )
            {
                throw new RuntimeException(app::get('topapi')->_('系统参数：版本号必填'), '10001');
            }

            //签名验证
            $shopId = kernel::single('topshopapi_token_shopex')->check($params);

            $this->setReturnFormat(input::get('format'));

            $objApiClass = $this->getApiClassByMethod(input::get('method'), $version);

            //验证api调用参数
            $apiParams = $this->parseParams($objApiClass[0], $params);
            $apiParams['shop_id'] = $shopId;

            //运行
            $response = $this->run($objApiClass, $apiParams);
        }
        catch( \LogicException $e )
        {
            return $this->__sendError($e->getMessage(), $e->getCode());
        }
        catch( \RuntimeException $e )
        {
            if (config::get('app.debug'))
            {
                $msg = $e->getMessage();
            }
            else
            {
                $msg = '系统繁忙，请重试';
            }
            return $this->__sendError($e->getMessage(), $e->getCode());
        }
        catch( \Exception $e)
        {
            if (config::get('app.debug'))
            {
                $msg = $e->getMessage();
            }
            else
            {
                $msg = '系统错误，服务暂不可用，请联系平台';
            }

            return $this->__sendError($msg, $e->getCode());
        }

        if( is_string($response) )
        {
            if (config::get('app.debug'))
            {
                $msg = '返回数据不能为字符串，请改为数组';
            }
            else
            {
                $msg = '系统繁忙，请重试';
            }
            return $this->__sendError($msg);
        }

        return $this->response($response);
    }

    private function __sendError($msg, $code)
    {
        if( !$msg ) $msg = 'API调用错误，必须返回错误信息';

        if( !$code ) $code = '10000';

        return $this->response('', $msg, $code);
    }

    /**
     * response
     *
     * @param  boolean $realpath
     * @return base_view_object_interface | string
     */
    final public function response($data, $message='', $code=0 )
    {
        $result = [
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'result' => $data,
        ];

        $status = ($code == 0) ? 'success' : 'fail';
        $this->__apiLogEnd($status, $result);

        switch($this->format) {
            case 'json':
                kernel::single('topshopapi_format_json')->formatData($result);
                break;
            case 'xml':
                kernel::single('topshopapi_format_xml')->formatData($result);
                break;
            case 'jsonp':
                kernel::single('topshopapi_format_jsonp', $this->params['callback'])->formatData($result);
                break;
            default:
                kernel::single('topshopapi_format_json')->formatData($result);
        }
    }

    public function run($objApiClass, $params)
    {
        return call_user_func($objApiClass, $params);
    }

    public function getApiClassByMethod($method, $version)
    {
        $method = trim($method);

        $shopApi = config::get('topshopapi.routes.'.$version);
        if( !$shopApi )
        {
            throw new RuntimeException('该版本号不存在API', 10002);
        }

        if( !in_array($method, array_keys($shopApi)) )
        {
            throw new RuntimeException('找不到API:' . $method);
        }

        list($class, $fun) = $this->parseClassCallable($shopApi[$method]['uses']);
        $objclass = new $class();
        if(! $objclass instanceof topshopapi_interface_api)
        {
            throw new RuntimeException($objclass.' must implements the topshopapi_interface_api', 10004);
        }

        //判断下方法是否存在
        if( !method_exists( $objclass, $fun ) )
        {
            throw new RuntimeException('找不到方法 :' . $fun, 10003);
        }

        return [$objclass, $fun];
    }

    protected function parseClassCallable($apiHandler)
    {
        $segments = explode('@', $apiHandler);

        return [$segments[0], count($segments) == 2 ? $segments[1] : 'handle'];
    }

    /**
     * 处理参数，并且验证参数
     */
    public function parseParams($class, $params)
    {
        $data = ecos_parseApiParams($class, $params);
        return $data;
    }

    public function setReturnFormat($format)
    {
        $this->format = $format ? $format : 'json';
    }

    //API日志创建
    private function __apiLogStart()
    {
        $params = input::get();

        //加入api日志
        $logData['apilog_id'] = uniqid();
        $logData['msg_id'] = $params['msg_id'] ? $params['msg_id'] : $params['task'];
        $logData['api_platform'] = 'shop';
        $logData['worker'] = $params['method'];
        $logData['params'] = [
                'api_params' => $params,
            ];
        try
        {
            $this->runtimeStart = microtime(true);
            $this->apilogId = kernel::single('system_apilog')->create('response', $logData);
        }
        catch( Exception $e)
        {
            logger::info('apilog_data : '. var_export($logData,true));
        }

        return true;
    }

    private function __apiLogEnd($status, $result)
    {
        try
        {
            $this->runtimeStop= microtime(true);
            $runtime = round(($this->runtimeStop - $this->runtimeStart) , 4);
            kernel::single('system_apilog')->update($this->apilogId, $status, $result, $runtime);
        }
        catch( Exception $e)
        {
            logger::info('update_apilog_data '.$status.':'. var_export($result,true));
        }

        return true;
    }
}

