<?php
/**
 * -- sysdecorate.coupon.add
 * -- 添加商品展示挂件
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysdecorate_api_coupon_add {

    public $apiDescription = '添加商品展示挂件';

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
            'widgets_id' => ['type'=>'int',       'valid'=>'integer', 'desc'=>'挂件ID'],
            'shop_id'    => ['type'=>'int',       'valid'=>'required|integer', 'desc'=>'店铺ID'],
            'page_name'  => ['type'=>'string',    'valid'=>'required', 'desc'=>'页面名称'],
            'platform'   => ['type'=>'string',    'valid'=>'required|in:pc,wap,app', 'desc'=>'平台名称'],
            'order_sort' => ['type'=>'int',       'valid'=>'required|integer', 'desc'=>'排序'],
            'list'       => ['type' => 'jsonArray', 'valid' => 'required', 'desc' => '优惠券数据', 'msg'=>'请填选择优惠券', 'params'=>array(
                'coupon_id'=> ['type'=>'int',    'valid'=>'required|integer', 'desc'=>'优惠券ID'],
                'amount'   => ['type'=>'string', 'valid'=>'required', 'desc'=>'优惠券金额'],
                'rule'     => ['type'=>'string', 'valid'=>'required', 'desc'=>'优惠券说明'],
                'deadline' => ['type'=>'string', 'valid'=>'required', 'desc'=>'优惠券有效时间'],
            )],
        );
        return $return;
    }

    /**
     * @desc 添加单图展示挂件
     */
    public function save($params)
    {
        $data['params'] = $params['list'];

        if( $params['widgets_id'] )
        {
            $data['widgets_id']   = $params['widgets_id'];
        }

        $data['order_sort']   = $params['order_sort'];
        $data['shop_id']      = $params['shop_id'];
        $data['widgets_type'] = 'coupon';//商品展示
        return kernel::single('sysdecorate_new_widgets')->setConfig($params['page_name'], $params['platform'], $data);
    }
}
