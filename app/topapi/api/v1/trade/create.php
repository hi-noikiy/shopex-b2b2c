<?php
/**
 * topapi
 *
 * -- trade.create
 * -- 创建订单
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_trade_create implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '创建订单';

    /**
     * 定义API传入的应用级参数
     * @return string payment_type 支付类型（online or offline）
     * @return string payment_id 支付单号（仅在payment_type为online时出现）
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'mode'          => ['type'=>'string',  'valid'=>'required|in:cart,fastbuy', 'desc'=>'购物车类型', 'example'=>'cart'],
            'md5_cart_info' => ['type'=>'string', 'valid'=>'required', 'desc'=>'购物车数据校验'],
            'addr_id'       => ['type'=>'string', 'valid'=>'required', 'desc'=>'收货地址', 'msg'=>'请选择收货地址'],
            'payment_type'  => ['type'=>'string', 'valid'=>'required', 'desc'=>'支付方式', 'msg'=>'请选择支付方式'],
            'source_from'   => ['type'=>'string', 'valid'=>'in:pc,wap,app', 'desc'=>'使用平台 pc电脑端 wap手机端 app手机App端'],
            'shipping_type' => ['type'=>'jsonArray', 'valid'=>'required', 'example'=>'', 'desc'=>'每个店铺的配送方式 [{"shop_id":"3","type":"express"}]', 'params' => [
                'shop_id' => ['type'=>'string',  'valid'=>'required', 'example'=>'', 'desc'=>'店铺ID'],
                'type'    => ['type'=>'string',  'valid'=>'required', 'example'=>'', 'desc'=>'配送方式'],
                'ziti_id' => ['type'=>'string', 'valid'=>'required_if:type,ziti',    'example'=>'', 'desc'=>'自提ID', 'msg'=>'请选择自提地址'],
            ]],
            'mark' => ['type'=>'jsonArray', 'valid'=>'', 'desc'=>'买家留言', 'params'=>[
                'shop_id' => ['type'=>'string',  'valid'=>'required', 'desc'=>'店铺ID'],
                'memo'    => ['type'=>'string',  'valid'=>'required', 'desc'=>'对应店铺买家留言'],
            ]],
            'invoice_type'    => ['type'=>'string', 'valid'=>'required:in,normal,vat,notuse','desc'=>'发票类型 normal普通发票，vat 增值税发票, notuse 不需要发票'],
            'invoice_content' => ['type'=>'jsonObject', 'valid'=>'required_if:invoice_type,normal,vat', 'desc'=>'发票数据内容', 'params'=>array(
                'title'                 => ['type'=>'string', 'valid'=>'required_if:invoice_type,normal|in:individual,unit', 'desc'=>'发票抬头类型 individual 个人,unit 企业'],
                'content'               => ['type'=>'string', 'valid'=>'required_if:invoice_type,normal', 'desc'=>'发票抬头'],
                'company_name'          => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司名称',     'msg'=>'请输入公司名称'],
                'company_address'       => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司地址',     'msg'=>'请输入公司地址'],
                'registration_number'   => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'纳税人识别号', 'msg'=>'请输入纳税人识别号'],
                'bankname'              => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'开户银行',     'msg'=>'请输入开户银行'],
                'bankaccount'           => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'开户银行帐号', 'msg'=>'请输入开户银行帐号'],
                'company_phone'         => ['type'=>'string', 'valid'=>'required_if:invoice_type,vat', 'desc'=>'公司电话',     'msg'=>'请输入公司电话'],
            )],
            'use_points' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'desc'=>'使用的积分值'],
        );

        return $return;
    }

    /**
     * @return string payment_type 在线支付或者线下支付
     * @return string payment_id 支付单号
     */
    public function handle($params)
    {
        $params['user_name']       = app::get('topapi')->rpcCall('user.get.account.name', ['user_id' =>$params['user_id']]);
        $params['source_from']     = $params['source_from'] ?: 'app';
        $params['shipping_type']   = json_encode($params['shipping_type']);
        if( $params['mark'] )
        {
            $params['mark']            = json_encode($params['mark']);
        }
        if( $params['invoice_content'] )
        {
            $params['invoice_content'] = json_encode($params['invoice_content']);
        }
        $createFlag  = app::get('topapi')->rpcCall('trade.create',$params);

        $paymentType = $params['payment_type'];
        if($paymentType == 'online')
        {
            $requestParamsForPayment['tid'] = $createFlag;
            $requestParamsForPayment['user_id'] = $params['user_id'];
            $requestParamsForPayment['user_name'] = $params['user_name'];

            $paymentId = kernel::single('topapi_payment')->getPaymentId($requestParamsForPayment);
        }

        return ['payment_type'=>$paymentType, 'payment_id'=>$paymentId];
    }
}

