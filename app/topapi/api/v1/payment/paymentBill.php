<?php
/**
 * topapi
 *
 * -- payment.pay.getpayment
 * -- 获取会员基本信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_payment_paymentBill implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取支付单';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'payment_id' => ['type'=>'string', 'valid'=>'required', 'desc'=>'支付单号', 'msg'=>'请输入支付单号'],
            ];
    }

    /**
     * @return array payment 支付单
     */
    public function handle($params)
    {
        $paymentId = $params['payment_id'];
        $paymentFilter = [
            'payment_id' =>$paymentId,
            'fields'=>'*',
            ];
        $paymentBill = app::get('topwap')->rpcCall('payment.bill.get',$paymentFilter);
        if($params['user_id'] != $paymentBill['user_id'] || empty($paymentBill))
            throw new LogicException(app::get('topapi')->_('支付单不存在！'));

        return ['payment'=>$paymentBill];
    }
}

