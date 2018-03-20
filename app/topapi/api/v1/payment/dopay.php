<?php
/**
 * topapi
 *
 * -- payment.pay.do
 * -- 获取会员基本信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_payment_dopay implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '去支付';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'payment_id' => ['type'=>'string', 'valid'=>'required', 'desc'=>'支付单号', 'msg'=>'请输入支付单号'],
            'pay_app_id' => ['type'=>'string','valid'=>'required_without:hongbao_ids', 'desc'=>'支付方式', 'msg'=>'请输入支付单号'],
            'deposit_password' => ['type'=>'string','valid'=>'required_with:hongbao_ids', 'desc'=>'支付密码','msg'=>'请输入支付密码|请输入支付密码'],
//          'tids' => ['type'=>'string','valid'=>'required', 'description'=>'被支付的订单号集合,用逗号隔开', 'default'=>'', 'example'=>'1241231213432,2354234523452'],
//          'platform' => ['type'=>'string','valid'=>'required', 'description'=>'来源平台（wap、pc）', 'default'=>'pc', 'example'=>'pc'],
//          'money' => ['type'=>'string','valid'=>'required', 'description'=>'支付金额', 'default'=>'', 'example'=>'234.50'],
            'hongbao_ids' => ['type'=>'string','valid'=>'required_without:pay_app_id', 'description'=>'使用支付的红包ID,用逗号隔开', 'default'=>'', 'example'=>'1,2,3'],
            ];
    }

    /**
     */
    public function handle($params)
    {
        $paymentId = $params['payment_id'];
        $paymentFilter = [
            'payment_id' =>$paymentId,
            'fields'=>'*',
            ];
        $paymentBill = app::get('topwap')->rpcCall('payment.bill.get',$paymentFilter);
        $requestParams = [];
        $requestParams['payment_id'] = $params['payment_id'];
        $requestParams['pay_app_id'] = $params['pay_app_id'];
        $requestParams['platform'] = 'app';
        $requestParams['money'] = $paymentBill['money'];
        $requestParams['deposit_password'] = $params['deposit_password'];
        $requestParams['user_id'] = $params['user_id'];
        $requestParams['tids'] = implode(',',  array_keys($paymentBill['trade']));
        $requestParams['hongbao_ids'] = $params['hongbao_ids'];

        echo app::get('topwap')->rpcCall('payment.trade.pay',$requestParams);
        exit;
    }
}

