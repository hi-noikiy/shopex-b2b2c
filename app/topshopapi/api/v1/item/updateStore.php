<?php

class topshopapi_api_v1_item_updateStore implements topshopapi_interface_api {

    public $apiDescription = "回写库存，最多更新批量更新50条商品库存";

    public function setParams()
    {
        return array(
            'list_quantity' => ['type'=>'string','valid'=>'required','description'=>'库存列表的json格式[{"bn"=>,"quantity"=>}](最多50条),bn为sku_bn，不是商品bn','example'=>'[{"bn":"S558FBDE4EE0E901","quantity":100},{"bn":"S558FBDE4EE0E902","quantity":100}]','default'=>''],
        );
    }

    public function handle($params, $type='oms')
    {
        return app::get('topshopapi')->rpcCall('item.shop.store.update', $params);
    }
}

