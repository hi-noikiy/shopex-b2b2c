<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * open.shop.shopex.bind.list
 */
class sysopen_api_open_shopex_list {

    public $apiDescription = "获取店铺绑定列表";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'    => ['type'=>'int', 'valid'=>'required', 'example'=>'1','desc'=>'店铺ID'],
        );
        return $return;
    }

    public function handle($params)
    {
        if($params['shop_id'] > 0)
        {
            return app::get('sysopen')->model('shopexProduct')->getList('*', ['shop_id'=>$params['shop_id']]);
        }
        else
        {
            $nodeInfo = sysopen_shopexnode::getNodeInfo();
            $data = app::get('sysopen')->model('platform_bind')->getList('*');

            foreach($data as $key=>$value)
            {
                $data[$key]['node_id'] = $nodeInfo['node_id'];
            }
            return $data;
        }
    }
}

