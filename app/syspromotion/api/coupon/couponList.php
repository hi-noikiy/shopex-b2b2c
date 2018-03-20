<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取多条优惠券列表
 * promotion.coupon.list
 */
final class syspromotion_api_coupon_couponList {

    public $apiDescription = '获取指定店铺的优惠券列表';

    public function getParams()
    {
        $return['params'] = array(
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID'],
            'coupon_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'优惠券id'],
            'coupon_name' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'优惠券名称'],
            'coupon_status' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'优惠券状态'],
            'is_valid' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'可用优惠券'],
            'is_cansend' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'可领取优惠券'],
            'platform' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'优惠券适用平台'],
        );

        return $return;
    }

    /**
     * 获取优惠券列表
     */
    public function couponList($params)
    {
        $objMdlCoupon = app::get('syspromotion')->model('coupon');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $filter = array('shop_id'=>$params['shop_id']);

        // 平台未选择则默认全选
        if( $params['platform'] == 'pc' )
        {
            $filter['used_platform'] = array('0', '1');
        }
        elseif( $params['platform'] == 'wap' )
        {
            $filter['used_platform'] = array('0', '2');
        }
        elseif( $params['platform'] == 'app' )
        {
            $filter['used_platform'] = array('0', '3');
        }
        else
        {
            $filter['used_platform'] = array('0','1','2','3');
        }
        // 获取有效可使用优惠券
        if($params['is_valid'])
        {
            $filter['canuse_start_time|lthan'] = time();
            $filter['canuse_end_time|than'] = time();
            $filter['coupon_status'] = 'agree';
        }
        // 获取有效可领取优惠券
        if($params['is_cansend'])
        {
            $filter['cansend_start_time|lthan'] = time();
            $filter['cansend_end_time|than'] = time();
            $filter['coupon_status'] = 'agree';
        }

        $couponCount = $objMdlCoupon->count($filter);
        if(!$couponCount)
        {
            $result = array(
                    'coupons' => array(),
                    'count' => 0,
            );

            return $result;
        }
        $pageTotal = ceil($couponCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' coupon_id DESC';
        $couponData = $objMdlCoupon->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $result = array(
            'coupons' => $couponData,
            'count' => $couponCount,
        );

        return $result;
    }

}

