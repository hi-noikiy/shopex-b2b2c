<?php

class systrade_api_voucher_cartCancel {

    /**
     * 接口作用说明
     * trade.cart.voucher.cancel
     */
    public $apiDescription = '订单结算取消购物券';

    public $use_strict_filter = true;

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id'      => ['type'=>'int',    'valid'=>'required|integer', 'example'=>'','description'=>'用户id'],
            'platform' => ['type'=>'string', 'valid'=>'required|in:pc,wap,app', 'example'=>'','description'=>'使用平台'],
        );

        return $return;
    }

    /**
     * 选择的优惠券放入购物车优惠券表
     *
     * @param array $params 接口传入参数
     * @return array
     */
    public function handle($params)
    {
        $userId = $params['user_id'];
        $objLibCart = kernel::single("systrade_cart_voucher");
        return  $objLibCart->cancelVoucherCart($userId, $params['platform']);
    }
}

