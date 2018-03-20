<?php

/**
 * store.php 
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysitem_search_weight {

    public function saveSearchWeightRule($params)
    {
        $objMdlRule = app::get('sysitem')->model('item_search_rule');
        if($params['rule_id']){
            $data['rule_id'] = intval($params['rule_id']);
        }
        else
        {
            $data['created_time'] = time();
        }
        $data['name'] = $params['name'];
        $data['desc'] = $params['desc'];

        $validator = validator::make(
            [
                'goods_quality' => $params['rule']['goods_quality'] , 
                'goods_updown' => $params['rule']['goods_updown'],
                'goods_maintenance' => $params['rule']['goods_maintenance'],
                'shop' => $params['rule']['shop'],
            ],
            [
                'goods_quality' => 'required|numeric|integer|between:0,100',
                'goods_updown' => 'required|numeric|integer|between:0,100',
                'goods_maintenance' => 'required|numeric|integer|between:0,100',
                'shop' => 'required|numeric|integer|between:0,100',
            ],
            [
                'goods_quality' => '商品质量值的范围是0-100的正整数',
                'goods_updown' => '商品上下架值的范围是0-100的正整数',
                'goods_maintenance' => '商品维护值的范围是0-100的正整数',
                'shop' => '店铺值的范围是0-100的正整数',
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new LogicException( $error[0] );
            }
        }

        $allper = array_sum($params['rule']);
        if($allper!=100){
            $msg = app::get('sysitem')->_('各项百分比总和为100');
            throw new \LogicException($msg);
        }
        $data['rule'] = json_encode($params['rule']);

        return $objMdlRule->save($data);
    }

    public function saveShopWeight($params)
    {
        if(!$params['shop_id'])
        {
            throw new \LogicException(app::get('sysitem')->_('缺少店铺ID'));
        }
        $shopInfo = app::get('sysshop')->model('shop')->getRow('shop_type', ['shop_id'=>$params['shop_id']]);
        $searchweight = config::get('searchweight.shop.shoptype_'.$shopInfo['shop_type']);
        $maxweight = 1000 - $searchweight;
        if(  ($params['custom_weight']) < 0 || ($params['custom_weight'] > $maxweight) ){
            throw new \LogicException(app::get('sysitem')->_('自定义权重分与店铺类型权重分总分范围是0到'.$maxweight.'的正整数！'));
        }

        $data = ['shop_id'=>$params['shop_id'], 'custom_weight'=>$params['custom_weight']];
        return app::get('sysitem')->model('item_search_shopweight')->save($data);
    }

}