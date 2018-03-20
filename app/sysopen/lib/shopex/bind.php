<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysopen_shopex_bind {

    /**
     * 绑定通过或拒绝，或者解绑回调通知
     *
     * array (
     *    'node_id' => '1845914536',
     *    'node_type' => 'ecos.ome',
     *    'shop_name' => '21321312',
     *    'status' => 'unbind',
     *    'certi_ac' => 'cf1dafdf0af2f689392138c5429255bb',
     * )
     *
     */
    public function bindCallback()
    {
        $data = input::get();

        logger::info('bind shopexMatrix callback data:'.var_export($data, true));

        if( !$data['node_id'] || !$data['shop_name'] )
        {
            return false;
        }

        $filter = [
            'to_node_id' => $data['node_id'],
            'shop_name'=> $data['shop_name'],
        ];

        $shopexProductInfo = app::get('sysopen')->model('shopexProduct')->getRow('id,shop_id', $filter);
        if( $shopexProductInfo )
        {
            $objMdlShopexMatrix = app::get('sysopen')->model('shopexMatrix');
            $nodeInfo = $objMdlShopexMatrix->getRow('shop_id,node_id,certi_id,node_token', ['shop_id'=>$shopexProductInfo['shop_id']]);
        }
        else
        {
            logger::error('Error bind shopexMatrix callback not found:'.var_export($data, true));
            return false;
        }

        if( $data['certi_ac'] != $this->__getCertiAC($data, $nodeInfo['node_token']) )
        {
            logger::error('Error bind shopexMatrix callback check AC fail:'.var_export($data, true));
            return false;
        }

        if( in_array($data['status'], ['unbind','binded']))
        {
            //拒绝绑定或者解除绑定，则将绑定关系删除
            if($shopexProductInfo['id'])
                app::get('sysopen')->model('shopexProduct')->delete(['id'=>$shopexProductInfo['id']]);
        }
        else
        {
            if($shopexProductInfo['id'])
                app::get('sysopen')->model('shopexProduct')->update(['bind_status'=>$data['status'],'is_valid'=>1,'api_ver'=>$data['api_v']], ['id'=>$shopexProductInfo['id']]);
        }

        return true;
    }

    public function platformBindCallback()
    {
        $data = input::get();

        logger::info('platform bind shopexMatrix callback data:'.var_export($data, true));

        if( !$data['node_id'] )
        {
            return false;
        }
        $shopexNod = sysopen_shopexnode::getNodeInfo();
        $shopexProductInfo = app::get('sysopen')->model('platform_bind')->getRow('id,to_node_id,shop_name', ['to_node_id'=>$data['node_id'] ]);

        if( $data['certi_ac'] != $this->__getCertiAC($data, $shopexNod['token']) )
        {
            logger::error('Error bind shopexMatrix callback check AC fail:'.var_export($data, true) . ' with token ' . $shopexNod['token']);
            return false;
        }

        if( in_array($data['status'], ['unbind','binded']))
        {
            //拒绝绑定或者解除绑定，则将绑定关系删除
            if($shopexProductInfo['id'])
                app::get('sysopen')->model('platform_bind')->delete(['id'=>$shopexProductInfo['id']]);
        }
        else
        {
            if($shopexProductInfo['id'])
                app::get('sysopen')->model('platform_bind')->update(['bind_status'=>$data['status'],'is_valid'=>1,'api_ver'=>$data['api_v']], ['id'=>$shopexProductInfo['id']]);
        }

        return true;

    }

    /**
     * 申请绑定
     */
    public function applyBind($params)
    {
        if($params['shop_id'])
        {
            return $this->shopApplyBind($params);
        } else {
            return $this->platformApplyBind($params);
        }
    }

    public function platformApplyBind($params)
    {
        //获取平台节点信息
        $nodeInfo = sysopen_shopexnode::getNodeInfo();
        $callBackUrl = kernel::openapi_url('openapi.shopex_product_bind', 'platformBindCallback');
        $apiUrl = sysopen_shopexnode::getPlatformApiUrl();

        $count = app::get('sysopen')->model('platform_bind')->count(['to_node_id'=>$params['to_node_id']]);
        if( $count ) throw new LogicException('该节点已存在绑定');

        $data['app']           = 'app.applyNodeBind';
        $data['node_id']       = $nodeInfo['node_id'];
        $data['from_certi_id'] = $nodeInfo['license_id'];
        $data['callback']      = kernel::openapi_url('openapi.shopex_product_bind', 'platformBindCallback');
        $data['api_url']       = sysopen_shopexnode::getPlatformApiUrl();
        $data['node_type']     = $params['node_type'];
        $data['to_node']       = $params['node_id'];
        $data['shop_name']     = $params['title'];
        $data['certi_ac']      = $this->__getCertiAC($data, $nodeInfo['token']);

        logger::info('platform bind shop request : '.var_export($data, true));
        $response = client::post(config::get('link.matrix_relation_api'), ['body' => $data])->json();
        logger::info('platform bind shop response : '.var_export($response, true));
        if( $response['res'] == 'fail')
        {
            throw new LogicException($response['msg']['errorDescription']);
        }

        $saveData = [
            'shop_name'   => $params['title'],
            'node_type'   => $params['node_type'],
            'api_ver'     => $response[''],
            'to_node_id'  => $params['node_id'],
            'bind_status' => 'wait',
            'is_valid'    => '0',//默认开启
        ];
        return app::get('sysopen')->model('platform_bind')->insert($saveData);
    }

    public function shopApplyBind($params)
    {
        $objMdlShopexMatrix = app::get('sysopen')->model('shopexMatrix');
        $nodeInfo = $objMdlShopexMatrix->getRow('shop_id,node_id,certi_id,node_token', ['shop_id'=>$params['shop_id']]);

        $count = app::get('sysopen')->model('shopexProduct')->count(['shop_id'=>$params['shop_id'],'to_node_id'=>$params['to_node_id']]);
        if( $count ) throw new LogicException('该节点已存在绑定');

        $data['app']           = 'app.applyNodeBind';
        $data['node_id']       = $nodeInfo['node_id'];
        $data['from_certi_id'] = $nodeInfo['certi_id'];
        $data['callback']      = kernel::openapi_url('openapi.shopex_product_bind', 'bindCallback');
        $data['api_url']       = sysopen_shopexnode::getShopApiUrl();
        $data['node_type']     = $params['node_type'];
        $data['to_node']       = $params['to_node_id'];
        $data['shop_name']     = $params['shop_name'];
        $data['certi_ac'] = $this->__getCertiAC($data, $nodeInfo['node_token']);

        $response = client::post(config::get('link.matrix_relation_api'), ['body' => $data])->json();
        if( $response['res'] == 'fail')
        {
            throw new LogicException($response['msg']['errorDescription']);
        }

        $saveData = [
            'shop_id'     => $params['shop_id'],
            'shop_name'   => $params['shop_name'],
            'node_type'   => $params['node_type'],
            'node_id'     => $nodeInfo['node_id'],
            'to_node_id'  => $params['to_node_id'],
            'api_ver'     => $response[''],
            'bind_status' => 'wait',
            'is_valid'    => '0',//默认开启
        ];

        return app::get('sysopen')->model('shopexProduct')->insert($saveData);

    }


    /**
     *  查看绑定关系 iframe 方式
     */
    public function showBind($shopId)
    {
        $callBackUrl = '';
        $apiUrl = '';
        if( $shopId )
        {
            $objMdlShopexMatrix = app::get('sysopen')->model('shopexMatrix');
            $nodeInfo = $objMdlShopexMatrix->getRow('shop_id,node_id,certi_id,node_token', ['shop_id'=>$shopId]);
            $callBackUrl = kernel::openapi_url('openapi.shopex_product_bind', 'bindCallback');
            $apiUrl = sysopen_shopexnode::getShopApiUrl();
        }
        else//平台方式
        {
            //获取平台节点信息
            $nodeInfo = sysopen_shopexnode::getNodeInfo();
            $nodeInfo['certi_id'] = $nodeInfo['license_id'];
            $nodeInfo['node_token'] = $nodeInfo['token'];
            $callBackUrl = kernel::openapi_url('openapi.shopex_product_bind', 'platformBindCallback');
            $apiUrl = sysopen_shopexnode::getPlatformApiUrl();
        }
        $params['source']   = 'accept';
        $params['certi_id'] = $nodeInfo['certi_id'];
        $params['node_id']  = $nodeInfo['node_id'];
        $params['sess_id']  = kernel::single('base_session')->sess_id();
        $params['callback'] = $callBackUrl;
        $params['api_url']  = $apiUrl;

        //参与验签的参数
        $acParams = [
            'node_id'  => $params['node_id'],
            'certi_id' => $params['certi_id'],
            'sess_id'  => $params['sess_id'],
        ];

        $params['certi_ac'] = $this->__getCertiAC($acParams, $nodeInfo['node_token']);
        return config::get('link.matrix_relation_url').'?'. http_build_query($params);

    }

    private function __getCertiAC($data, $token)
    {
        ksort($data);
        foreach($data as $key => $value)
        {
            if( $key != 'certi_ac' )
            {
                $str.=$value;
            }
        }
        return md5($str.$token);
    }
}
