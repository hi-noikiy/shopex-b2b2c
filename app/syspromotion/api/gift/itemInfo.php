<?php
// promotion.gift.item.info
class syspromotion_api_gift_itemInfo {

    public $apiDescription = "获取参与活动的商品详情";

    public function getParams()
    {
        $data['params'] = array(
            'gift_id' => ['type'=>'int',    'valid'=>'sometimes|required|integer', 'description'=>'赠品活动id'],
            'item_id' => ['type'=>'string', 'valid'=>'required',                   'description'=>'参加活动的商品id,多个商品id请通过半角逗号隔开，如 1,2,3 '],
            'valid'   => ['type'=>'bool',   'valid'=>'boolean',                    'description'=>'活动状态'],
        );
        return $data;
    }

    public function getInfo($params)
    {
        $data = array();
        $objItemGift = kernel::single('syspromotion_data_object')->setPromotion('gift');
        if($params['valid'])
        {
            $itemFilter['start_time|lthan'] = time();
            $itemFilter['end_time|than'] = time();
            $itemFilter['status'] = '1';
            $data = $objItemGift->getGiftItemByItemId($params['item_id'],$itemFilter);
        }
        else
        {
            $itemFilter['gift_id'] = $params['gift_id'];
            if($itemFilter['gift_id'])
            {
                $data = $objItemGift->getGiftItemByItemId($params['item_id'],$itemFilter);
            }
        }
        foreach($data as $k=>&$v)
        {
            if($v['gift_id'])
            {
                $gift_info = $objItemGift->getPromoitonInfo($v['gift_id'], '*');
                $v = array_merge($v, $gift_info);
            }
            if($v['gift_status'] == 'cancel')
            {
                unset($data[$k]);
            }
        }

        return $data;
    }
}
