<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 批量获取商品的促销信息
 * item.promotion.list
 */
class sysitem_api_promotion_itemPromotionTagList {

    /**
     * 接口作用说明
     */
    public $apiDescription = '批量获取商品的促销信息';

    public $use_strict_filter = true; // 是否严格过滤参数

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'item_ids' => ['type'=>'string', 'valid'=>'required', 'description'=>'商品ID,以半角逗号隔开，如 1,2,3 '],
        );

        return $return;
    }

    /**
     * 批量获取商品的促销信息
     */
    public function itemPromotionTagList($params)
    {
        $itemIds = [];
        $item_ids = explode(',', $params['item_ids']);
        array_walk($item_ids, function($tmpitemid) use (&$itemIds) {
            if($tmpitemid)
            {
                $itemIds[] = intval($tmpitemid);
            }
        });
        $itemIds = array_unique($itemIds);

        $objMdlItemPromotionTag = app::get('sysitem')->model('item_promotion');
        $result = $objMdlItemPromotionTag->getList('*', array('item_id'=>$itemIds));

        return $result;
    }
}

