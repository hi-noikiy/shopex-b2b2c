<?php
/**
 * item.get
 */
class topshopapi_api_v1_item_get implements topshopapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取商品详细信息';

    private $fromNodeType = [
        'oms' => 'topshopapi_oms_item_itemInfo',
    ];

    public function setParams()
    {
        return [
            'item_id' => ['type'=>'int',  'valid'=>'required','desc'=>'商品编号','example'=>'2'],
            'fields' => ['type'=>'string','valid'=>'',        'desc'=>'要获取的商品字段集','example'=>'title,item_store.store,item_status.approve_status'],
        ];
    }

    /**
     * 获取单笔交易数据
     *
     * @param array $params 接口传入参数
     * @return array
     */
    public function handle($params, $fromNodeType='oms')
    {
        if( !$params['fields'] )
        {
            $params['fields'] = '*,item_store,sku,item_status';
        }

        $data = app::get('topshopapi')->rpcCall('item.get', $params);

        if( $fromNodeType && $this->fromNodeType[$fromNodeType] )
        {
            $class = $this->fromNodeType[$fromNodeType];
            return kernel::single($class)->handle($data);
        }

        return $data;
    }
}
