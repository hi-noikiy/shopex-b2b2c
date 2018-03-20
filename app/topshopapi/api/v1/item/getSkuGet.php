<?php
/**
 * 获取指定商品的货品列表
 * item.sku.get
 */
class topshopapi_api_v1_item_getSkuGet implements topshopapi_interface_api {

    public $apiDescription = "根据sku_id获取货品数据";

    private $fromNodeType = [
        'oms' => 'topshopapi_oms_item_skuInfo',
    ];

    public function setParams()
    {
        return array(
            'sku_id' => ['type'=>'string','valid'=>'required','description'=>'货品ID','example'=>'2'],
            'item_id' => ['type'=>'int','valid'=>'','description'=>'商品id','example'=>'2'],
        );
    }

    public function handle($params, $fromNodeType='oms')
    {
        $data = app::get('topshopapi')->rpcCall('item.sku.get', $params);

        if( $fromNodeType && $this->fromNodeType[$fromNodeType] )
        {
            $class = $this->fromNodeType[$fromNodeType];
            return kernel::single($class)->handle($data);
        }

        return $data;
    }
}

