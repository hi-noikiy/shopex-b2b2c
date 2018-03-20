<?php
class sysshop_api_getShopList{

    public $apiDescription = "获取店铺列表";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'description'=>'需要的字段'],
        );
        return $return;
    }
    public function getShopList($params)
    {
        $objMdlShop = app::get('sysshop')->model('shop');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $count = $objMdlShop->count();
        $list = $objMdlShop->getList('shop_id');

        $result = array(
            'data' => $list,
            'count' => $count,
        );

        return $result;
    }
}
