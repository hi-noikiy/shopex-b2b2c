<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取商家所有可用促销活动列表
 * promotion.promotion.list.get
 */
final class syspromotion_api_promotionList {

    public $apiDescription = '获取商家所有可用促销活动列表';
    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required',  'description'=>'店铺ID'],
            'used_platform' => ['type'=>'int','valid'=>'in:0,1,2,3','description'=>'使用平台'],
        );

        return $return;
    }

    /**
     *  获取商家促销活动列表
     * @param  array $params 筛选条件数组
     * @return array         返回促销活动列表
     */
    public function getList($params)
    {
        $filter = [
            'check_status' => 'agree',
            'start_time|lthan' => time(),
            'end_time|bthan' => time(),
        ];
        $fields = 'promotion_id, promotion_type, promotion_tag, shop_id, promotion_name, start_time, end_time';
        $orderBy = 'created_time desc';

        if(!$params['shop_id'])
        {
            throw new Exception(app::get('syspromotion')->_('店铺信息错误！'));      
        }else{
            $filter['shop_id'] = $params['shop_id'];
        }
        if($params['used_platform'] && $params['used_platform'] !=0)
        {
            $filter['used_platform|in'] = array(0,$params['used_platform']);
        }else{
            $filter['used_platform'] = $params['used_platform'];
        }
        //xy、fullminus、fulldiscount
        $objPromotions = app::get('syspromotion')->model('promotions');
        $promotionList = $objPromotions->getList($fields, $filter, 0, -1, $orderBy);

        //activity
        $activityParams = array(
            'shop_id' => $params['shop_id'],
            'activity_status' => 'starting',
            'valid_status' => '1',
            'verify_status' => 'agree',
         );
        $activityList = app::get('syspromotion')->rpcCall('promotion.activity.register.list', $activityParams);
        foreach ($activityList['data'] as $k => $v) {
            $activityData  = app::get('syspromotion')->rpcCall('promotion.activity.info',['activity_id'=> $v['activity_id'],'fields'=>'activity_id, activity_name, activity_tag, start_time,end_time']);
            $activity[$k]['promotion_id'] = $v['activity_id'];
            $activity[$k]['promotion_type'] = 'activity';
            $activity[$k]['promotion_tag'] = $activityData['activity_tag'];
            $activity[$k]['shop_id'] = $v['shop_id'];
            $activity[$k]['promotion_name'] = $activityData['activity_name'];
            $activity[$k]['start_time'] = $activityData['start_time'];
            $activity[$k]['end_time'] = $activityData['end_time'];
        }

        $pagedata['list'] = array_merge($promotionList, $activity);

        return $pagedata;
    }
}

