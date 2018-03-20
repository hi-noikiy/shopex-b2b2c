<?php

class topshopapi_api_v1_aftersales_refundapply_create implements topshopapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '创建退款申请单';

    public function setParams()
    {
        return array(
            'refund_bn'     => ['type'=>'string','valid'=>'required', 'description'=>'退款申请单编号，如果未填写则自动生成'],
            'aftersales_bn' => ['type'=>'string','valid'=>'', 'description'=>'售后申请单（退款关联的售后申请单编号）'],
            'tid'           => ['type'=>'string','valid'=>'required', 'description'=>'订单号'],
            'reason'        => ['type'=>'json',  'valid'=>'', 'description'=>'申请退款理由'],
            'total_price'   => ['type'=>'string','valid'=>'required', 'description'=>'申请退款的金额，取消订单不需要填写退款金额'],
        );
    }

    public function handle($params, $type='oms')
    {
        return app::get('topshopapi')->rpcCall('aftersales.refundapply.shop.add', $params);
    }
}


