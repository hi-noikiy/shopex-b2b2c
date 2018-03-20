<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * open.shop.node.get
 */
class sysopen_api_open_shopex_getNode {

    public $apiDescription = "获取店铺shopex体系节点ID";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required_without:node_id', 'example'=>'1','desc'=>'店铺ID'],
            'node_id' => ['type'=>'int', 'valid'=>'required_without:shop_id', 'example'=>'1','desc'=>'店铺节点号'],
        );
        return $return;
    }

    public function handle($params)
    {
        //这里判断一下是否是平台的nodeinfo
        $nodeInfo = sysopen_shopexnode::getNodeInfo();
        if( (isset($params['shop_id']) && $params['shop_id'] == 0) || $params['node_id'] == $nodeInfo['node_id'] )
        {
            $data['shop_id'] = 0;
            $data['node_id'] = $nodeInfo['node_id'];
            $data['node_token'] = $nodeInfo['token'];
            $data['certi_id'] = $nodeInfo['license_id'];
            $data['certi_token'] = base_certi::token();
            return $data;
        }else{
            $objMdlShopexMatrix = app::get('sysopen')->model('shopexMatrix');
            $data = $objMdlShopexMatrix->getRow('shop_id,node_id,node_token,certi_id,certi_token', $params);
            return $data;
        }
    }
}

