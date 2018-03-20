<?php
/**
 * topapi
 *
 * -- member.index
 * -- 会员中心首页数据统计
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_index implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '会员中心首页数据统计';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [];
    }

    /**
     * @return int wait_pay_num 待支付订单数量
     * @return int wait_send_goods_num 待发货订单数量
     * @return int wait_confirm_goods_num 待确认收货订单数量
     * @return int canceled_num 已取消订单数量
     * @return int notrate_num 待评价订单数量
     * @return int coupon_num 优惠券数量
     * @return int point 积分
     *
     * @return string cur_symbol.sign 货币符号
     * @return string cur_symbol.decimals 计算精度，保留小数点位数
     */
    public function handle($params)
    {
        $userId = $params['user_id'];
        // 会员等级信息
        $result['gradeInfo'] = app::get('topapi')->rpcCall('user.grade.basicinfo', ['user_id'=>$userId]);
        $result['gradeInfo']['grade_logo'] = base_storager::modifier($result['gradeInfo']['grade_logo'], 't');
        //获取订单各种状态的数量
        $result['wait_pay_num'] = app::get('topapi')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_PAY'));
        $result['wait_send_goods_num'] = app::get('topapi')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_SELLER_SEND_GOODS'));
        $result['wait_confirm_goods_num'] = app::get('topapi')->rpcCall('trade.count',array('user_id'=>$userId,'status'=>'WAIT_BUYER_CONFIRM_GOODS'));
        $cancelData = app::get('topapi')->rpcCall('trade.cancel.list.get',['user_id'=>$userId,'fields'=>'tid']);
        $result['canceled_num'] = $cancelData['total'];
        $result['notrate_num'] = app::get('topapi')->rpcCall('trade.notrate.count',array('user_id'=>$userId));

        //优惠劵数量
        $coupon = app::get('topapi')->rpcCall('user.coupon.count', ['user_id'=>$userId, 'is_valid'=>'1']);
        $result['coupon_num'] = $coupon['count'] ?: 0;

        //购物券数量
        $voucher = app::get('topwap')->rpcCall('user.voucher.list.get', ['user_id'=>$userId, 'is_valid'=>'1']);
        $result['voucher_num'] = $voucher['pagers']['total'] ?: 0;

        $point = app::get('topapi')->rpcCall('user.point.get', ['user_id'=>$userId]);
        $result['point'] = $point['point_count'] ?: 0;

        $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
        $result['cur_symbol'] = $cur_symbol;

        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$userId]);
        $result['username'] = $userInfo['username'] ? :( $userInfo['login_account'] ? : ( $userInfo['mobile'] ? : ( $userInfo['email'] ) ) );
        $result['hongbaoCount'] = app::get('topwap')->rpcCall('user.hongbao.count',['user_id'=>$userId]);

        return $result;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"gradeInfo":{"grade_id":5,"experience":15095,"grade_name":"白金会员","grade_logo":"http://images.bbc.shopex123.com/images/6f/ca/48/3449cbc3e2b21c505aac507e96500749f4431fc7.png"},"wait_pay_num":0,"wait_send_goods_num":7,"wait_confirm_goods_num":0,"canceled_num":18,"notrate_num":2,"coupon_num":4,"point":15095,"cur_symbol":{"sign":"￥","decimals":"3"}}}';
    }

}

