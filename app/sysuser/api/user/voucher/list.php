<?php
/**
 * user.voucher.list.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 用户领取购物券的列表
 */
final class sysuser_api_user_voucher_list {

    public $apiDescription = '用户领取购物券的列表';

    public $use_strict_filter = true;

    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id'   => ['type'=>'int',        'valid'=>'required|integer', 'example'=>'', 'desc'=>'用户ID必填'],
            'tid'       => ['type'=>'string',     'valid'=>'',         'example'=>'', 'desc'=>'当前订单使用的购物券'],
            'page_no'   => ['type'=>'int',        'valid'=>'',         'example'=>'1', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',        'valid'=>'',         'example'=>'20','desc'=>'每页数据条数,默认20条'],
            'fields'    => ['type'=>'field_list', 'valid'=>'',         'example'=>'',  'desc'=>'需要的字段'],
            'orderBy'   => ['type'=>'string',     'valid'=>'',         'example'=>'end_time desc', 'desc'=>'排序'],
            'is_valid'  => ['type'=>'int',        'valid'=>'in:0,1,2',  'example'=>'', 'desc'=>'获取是否有效的参数'],
            'platform'  => ['type'=>'string',     'valid'=>'in:pc,wap,app',  'example'=>'', 'desc'=>'购物券使用平台'],
        );

        return $return;
    }

    /**
     * @return string voucher_code 购物券号码
     * @return int user_id 会员ID
     * @return int shop_id 店铺ID
     * @return int voucher_id 会员购物券ID
     * @return string obtain_desc 领取方式
     * @return timestamp obtain_time 购物券获得时间
     * @return int tid 订单ID
     * @return string is_valid 会员购物券是否当前可用(0:已使用；1:有效；2:过期)
     * @return string used_platform 使用平台
     * @return timestamp start_time 生效时间
     * @return timestamp end_time 失效时间
     * @return number limit_money 满足条件金额
     * @return number deduct_money 优惠金额
     * @return string voucher_name 购物券名称
     */
    public function handle($params)
    {
        $objMdlUserVoucher = app::get('sysuser')->model('user_voucher');

        $params['fields'] = $params['fields']?: '*';

        $filter['user_id'] = $params['user_id'];

        if( $params['platform'] )
        {
            $filter['used_platform|has'] = $params['platform'];
        }

        if( $params['tid'] )
        {
            $filter['tid|has'] = $params['tid'];
        }

        if(isset($params['is_valid']))
        {
            //如果未使用
            if( $params['is_valid'] == 1 )
            {
                $filter['is_valid'] = $params['is_valid'];
                $filter['end_time|than']  = time();
            }
            //如果已过期
            elseif( $params['is_valid'] == 2 )
            {
                $filter['is_valid|noequal'] = '0';
                $filter['end_time|sthan'] = time();
            }
            else
            {
                //已使用
                $filter['is_valid'] = '0';
            }
        }

        $itemCount = $objMdlUserVoucher->count($filter);
        $pageTotal = ceil($itemCount/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 20;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
        $offset = ($currentPage-1) * $limit;
        $orderBy  = $params['orderBy'] ? $params['orderBy'] : 'obtain_time DESC';
        $aData = $objMdlUserVoucher->getList($params['fields'], $filter, $offset, $limit, $orderBy);

        $result['list'] = $aData;
        $result['pagers']['total'] = $itemCount;

        return $result;
    }
}

