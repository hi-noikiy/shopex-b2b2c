<?php

class topshopapi_api_v1_trade_delivery implements topshopapi_interface_api {

    public $apiDescription = "对指定订单进行发货";

    public function setParams()
    {
        return array(
            'tid'       => ['type'=>'int', 'valid'=>'required', 'example'=>'','desc'=>'订单号'],
            'corp_code' => ['type'=>'int', 'valid'=>'required','example'=>'','desc'=>'物流公司编号'],
            'corp_no'   => ['type'=>'int', 'valid'=>'', 'example'=>'','desc'=>'运单号'],
            'ziti_memo' => ['type'=>'int', 'valid'=>'', 'example'=>'','desc'=>'自提备注'],
            'seller_id' => ['type'=>'string','valid'=>'', 'example'=>'','desc'=>'店铺id'],
            'memo'      => ['type'=>'string','valid'=>'', 'desc'=>'备注','example'=>'1'],
        );
    }

    public function handle($params, $type='oms')
    {
        $params['logi_no']   = $params['corp_no'];
        $params['seller_id'] = 0;//表示OMS
        unset($params['corp_no']);
        $res = app::get('syslogistics')->rpcCall('trade.delivery',$params);
        return true;
    }
}

