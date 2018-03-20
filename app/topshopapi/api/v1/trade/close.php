<?php

class topshopapi_api_v1_trade_close  implements topshopapi_interface_api {

    public $apiDescription = "商家取消订单";

    public function setParams()
    {
        return  array(
            'tid'           => ['type'=>'string', 'valid'=>'required', 'example'=>'','desc'=>'订单id'],
            'cancel_reason' => ['type'=>'string', 'valid'=>'required|max:50', 'example'=>'', 'msg'=>'订单取消原因必须填写|订单取消原因不能超过50个字符','description'=>'订单取消原因'],
            'refund_bn'     => ['type'=>'string', 'valid'=>'', 'desc'=>'退款申请单编号'],
            'return_freight'=> ['type'=>'bool',   'valid'=>'string', 'desc'=>'是否返还运费("true":退运费；"false":不退运费)'],
        );
    }

    public function handle($params, $type='oms')
    {
        return app::get('topshopapi')->rpcCall('trade.cancel', $params);
    }
}
