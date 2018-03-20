<?php

class system_shopexMatrix {

    public function getBindList($shopId)
    {
        return app::get('system')->rpcCall('open.shop.shopex.bind.list', ['shop_id'=>$shopId]);
    }

    /**
     * 获取店铺node信息 function
     *
     * @return void
     */
    private function __getShopNodeInfo($shopId)
    {
        return app::get('system')->rpcCall('open.shop.node.get', ['shop_id'=>$shopId]);
    }

    public function notify($class, $shopId, $params)
    {
        if($shopId == 'platform') $shopId = 0;
        //如果商家没有开通node_id则不需要推送，直接返回true
        $nodeInfo = $this->__getShopNodeInfo($shopId);
        if( !$nodeInfo ) return true;

        //如果商家没有绑定 不需要推送
        $bindList = $this->getBindList($shopId);
        if( !$bindList ) return true;

        $objClass = kernel::single($class);
        //矩阵API名称
        $method = $objClass->apiMethod;

        //矩阵API应用参数
        $data = $objClass->handle($params);

        foreach( $bindList as $row )
        {
            //如果未绑定或者平台临时关闭，则不同步数据
            if( $row['bind_status'] != 'bind' || !$row['is_valid'] )
            {
                continue;
            }

            $systemParams['method'] = $method;
            $systemParams['certi_id'] = $nodeInfo['certi_id'];
            $systemParams['from_node_id'] = $row['node_id'];
            $systemParams['to_node_id'] = $row['to_node_id'];
            $systemParams['to_api_v'] = $row['api_ver'];
            $systemParams['v'] = '1.0';
            $systemParams['timestamp'] = time();
            $systemParams['format'] = 'json';

            $apiParams = array_merge($systemParams, $data);
            $apiParams['sign'] = $this->__sign($apiParams, $nodeInfo['node_token']);

            $this->__apiLogStart($method, $apiParams);

            $result = client::post(config::get('link.matrix_realtime_url'), ['body' => $apiParams, 'timeout'=>30])->json();

            $result['post_url'] = config::get('link.matrix_realtime_url');
            if( $result['rsp'] == 'fail' )
            {
                logger::info('同步数据到矩阵失败：'. var_export($result,true));
                $this->__apiLogEnd('fail', $result);
            }
            else
            {
                logger::info('同步数据到矩阵成功：'. var_export($result,true));
                $this->__apiLogEnd('success', $result);
            }

            return $result;
        }
    }

    private function __sign($params, $token)
    {
        return strtoupper(md5(strtoupper(md5($this->__assemble($params))).$token));
    }

    private function __assemble($params)
    {
        if(!is_array($params))  return null;

        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val)
        {
            if( $key == 'sign' ) continue;
            $sign .= $key . (is_array($val) ? $this->__assemble($val) : $val);
        }
        return $sign;
    }

    //API日志创建
    private function __apiLogStart($method, $params)
    {
        //加入api日志
        $logData['apilog_id'] = uniqid();
        $logData['msg_id'] = '';
        $logData['api_platform'] = 'shop';
        $logData['worker'] = $method;
        $logData['params'] = [ 'api_params' => $params ];
        try
        {
            $this->runtimeStart = microtime(true);
            $this->apilogId = kernel::single('system_apilog')->create('request', $logData);
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
            kernel::single('system_apilog')->update($this->apilogId, $status, $result, $runtime, $result['msg_id']);
        }
        catch( Exception $e)
        {
            logger::info('update_apilog_data '.$status.':'. var_export($result,true));
        }

        return true;
    }
}

