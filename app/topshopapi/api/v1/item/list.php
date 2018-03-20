<?php
/**
 * 获取单个商品的详细信息
 * item.list.get
 */
class topshopapi_api_v1_item_list implements topshopapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '商品id列表，多个item_id用逗号隔开';

    private $fromNodeType = [
        'oms' => 'topshopapi_oms_item_list',
    ];

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function setParams()
    {
        return array(
            'item_id' => ['type'=>'string','valid'=>'required','description'=>'商品编号','example'=>'2,3,4'],
            'fields' => ['type'=>'field_list','valid'=>'required','description'=>'要获取的商品字段集 item_id必填','example'=>'item_id,title,item_store.store,item_status.approve_status','default'=>''],
        );
    }

    public function handle($params, $fromNodeType='oms')
    {
        $list = app::get('topshopapi')->rpcCall('item.list.get', $params);

        if( $fromNodeType && $this->fromNodeType[$fromNodeType] )
        {
            $class = $this->fromNodeType[$fromNodeType];
            return kernel::single($class)->handle($list);
        }

        return $list;
    }
}

