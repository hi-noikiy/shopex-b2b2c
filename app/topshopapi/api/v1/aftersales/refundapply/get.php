<?php

class topshopapi_api_v1_aftersales_refundapply_get implements topshopapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '根据退款申请单refunds_id，获取单个退款申请单详情';

    public function setParams()
    {
        return array(
            'refunds_id' => ['type'=>'string','valid'=>'required', 'description'=>'退款申请单ID'],
        );
    }

    public function handle($params, $type='oms')
    {
        return app::get('topshopapi')->rpcCall('aftersales.refundapply.get', $params);
    }
}

