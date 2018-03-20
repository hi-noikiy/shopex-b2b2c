<?php

class topshopapi_api_v1_aftersales_refundapply_shopCheck implements topshopapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '商家审核退款申请单(OMS创建退款申请单后直接审核通过)';

    public function setParams()
    {
       return array(
            'refund_bn' => ['type'=>'string','valid'=>'required', 'description'=>'refund_bn退款申请单编码'],
            'status'    => ['type'=>'string','valid'=>'required', 'description'=>'审核状态 agree 通过，reject 拒绝'],
            'reason'    => ['type'=>'json',  'valid'=>'', 'description'=>'仅在审核不通过时填写该值,审核不通过原因'],
        );
    }

    public function handle($params, $type='oms')
    {
        return app::get('topshopapi')->rpcCall('aftersales.refundapply.shop.check', $params);
    }
}

