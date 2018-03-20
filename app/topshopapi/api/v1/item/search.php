<?php
/**
 * 接口作用说明 oms
 * item.search
 */
class topshopapi_api_v1_item_search implements topshopapi_interface_api {

    public $apiDescription = '根据条件获取商品列表';

    private $fromNodeType = [
        'oms' => 'topshopapi_oms_item_search',
    ];

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function setParams()
    {
        return array(
            'page_no' => ['type'=>'int','valid'=>'numeric','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'numeric','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的商品字段集','example'=>'','default'=>''],
        );
    }

    public function handle($params, $fromNodeType='oms')
    {
        if( !$params['fields'] )
        {
            $params['fields'] = 'store,item_id,bn,store,title,shop_cat_id,brand_id,image_default_id,barcode,cost_price,list_time,delist_time,approve_status,price,mkt_price,modified_time';
        }

        $list = app::get('topshopapi')->rpcCall('item.search', $params);

        if( $fromNodeType && $this->fromNodeType[$fromNodeType] )
        {
            $class = $this->fromNodeType[$fromNodeType];
            return kernel::single($class)->handle($list);
        }

        return $list;
    }
}
