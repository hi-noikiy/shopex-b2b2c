<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * open.shop.apply.node
 */
class sysopen_api_open_shopex_applyNode {

    public $apiDescription = "店铺申请shopex体系节点ID";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'example'=>'1','desc'=>'店铺ID'],
        );
        return $return;
    }

    public function handle($params)
    {
        $objMdlShopexMatrix = app::get('sysopen')->model('shopexMatrix');

        $url = sysopen_shopexnode::getUrl($params['shop_id']);
        //申请证书
        $result = base_certi::register($url, $params['shop_id']);
        if( !$result )
        {
            throw new LogicException('证书申请失败，请联系平台处理');
        }

        $certi = $result['info'];

        //申请node
        sysopen_shopexnode::setShopCertiId($certi['certificate_id'], $certi['token']);
        $node = sysopen_shopexnode::register($params['shop_id']);
        if( !$node )
        {
            throw new LogicException('节点申请失败，请联系平台处理');
        }

        $saveData = [
            'shop_id'  => $params['shop_id'],
            'certi_id' => $certi['certificate_id'],
            'certi_token' => $certi['token'],
            'url'     => $url,
            'node_id'  => $node['node_id'],
            'node_token'=> $node['token'],
        ];
        return $objMdlShopexMatrix->save($saveData);
    }
}


