<?php
/**
 * -- sysdecorate.goods.data.get
 * -- 获取商品展示挂件页面渲染数据
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysdecorate_api_goods_getViewData {

    public $apiDescription = '获取商品展示挂件页面渲染数据';

    public $use_strict_filter = true; // 是否严格过滤参数

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'shop_id'    => ['type'=>'int', 'valid'=>'required|integer', 'desc'=>'店铺ID'],
            'widgets_id' => ['type'=>'int', 'valid'=>'required|integer', 'desc'=>'挂件ID'],
            'showItemNum'=> ['type'=>'int', 'valid'=>'integer|max:20', 'desc'=>'需要显示的商品数量'],
            'fields'     => ['type'=>'string','valid'=>'', 'desc'=>'需要返回的商品字段'],
        );
        return $return;
    }

    /**
     * @desc 添加单图展示挂件
     */
    public function get($params)
    {
        $data = app::get('sysdecorate')->model('widgets_instance')->getRow('widgets_id,shop_id,params', array('shop_id'=>$shopId, 'widgets_id'=>$params['widgets_id'], 'widgets_type'=>$params['widgets_type']));
        $filter = unserialize($data['params']);

        $itemId = $filter['item_id'];
        $params['showItemNum'] = $params['showItemNum'] ? $params['showItemNum'] : 8;
        if( count($itemId ) > $params['showItemNum'] )
        {
            $itemId = array_slice($itemId, 0, $params['showItemNum']);
        }

        $itemParams['item_id'] = implode(',', $itemId);
        $itemParams['fields']  = $params['fields'] ? $params['fields'] : 'item_id,image_default_id,title,price,item_status.approve_status,item_store';
        $itemsList = app::get('sysdecorate')->rpcCall('item.list.get',$itemParams);
        return $itemsList;
    }
}
