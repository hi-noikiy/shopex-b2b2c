<?php
/**
 * topapi
 *
 * -- payment.pay.list
 * -- 获取会员基本信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_payment_createPayment implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '创建支付单';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'tid' => ['type'=>'string', 'valid'=>'required', 'desc'=>'订单号', 'msg'=>'请输入订单号'],
            'merge' => ['type'=>'bool', 'valid'=>'', 'desc'=>'合并支付', 'msg'=>'']
            ];
    }

    /**
     * @return string payment_id 支付单编号
     */
    public function handle($params)
    {
        $createPaymentParams = [];
        $createPaymentParams['tid'] = $params['tid'];
        $createPaymentParams['merge'] = $params['merge'] ? true : false;
        $createPaymentParams['user_id'] = $params['user_id'];

        $userInfo = app::get('topapi')->rpcCall('user.get.info', ['user_id'=>$params['user_id']]);

        $createPaymentParams['user_name'] = $userInfo['login_account'] ? : ($userInfo['mobile'] ? : $userInfo['email']);

        $paymentId = kernel::single('topapi_payment')->getPaymentId($createPaymentParams);

        return ['payment_id'=>$paymentId];
    }
}

