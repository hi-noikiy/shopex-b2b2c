<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 是否开启商家开发者中心
 * open.shop.isopen
 */
class sysopen_api_open_isOpen {

    public $apiDescription = "是否开启开发者中心";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array();
        return $return;
    }

    public function handle($params)
    {
        $shopexmatrixEnable = app::get('sysopen')->getConf('shopexmatrix.enable');
        $shopexNod = sysopen_shopexnode::getNodeInfo();

        //如果平台开启shopex体系矩阵 并且正常的获取到node_id那么则表示开启了shopex体系
        $result['shopexmatrixEnable'] = false;
        if( $shopexmatrixEnable && $shopexNod['node_id'] )
        {
            $result['shopexmatrixEnable'] = true;
        }

        //如果有开发者，则表示开发者模式页开启了
        $result['developEnable'] = false;
        if( app::get('sysopen')->model('develop')->count() )
        {
            $result['developEnable'] = true;
        }

        return $result;
    }
}


