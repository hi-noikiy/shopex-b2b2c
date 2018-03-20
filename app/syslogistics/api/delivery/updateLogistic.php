<?php
class syslogistics_api_delivery_updateLogistic {

    public $apiDescription = "物流配送信息";

    public function getParams()
    {
        $return['params'] = array(
            'delivery_id' =>['type'=>'string','valid'=>'required', 'description'=>'发货单流水编号','default'=>'','example'=>'1'],
            'shop_id' =>['type'=>'string','valid'=>'required', 'description'=>'店铺编号','default'=>'','example'=>'1'],
          //'template_id' =>['type'=>'string','valid'=>'required', 'description'=>'运费模板号','default'=>'','example'=>'1'],
            'corp_id' =>['type'=>'int','valid'=>'required', 'description'=>'物流公司id','default'=>'','example'=>'1', 'msg'=>'请选择物流公司!'],
          //'corp_code' =>['type'=>'string','valid'=>'required', 'description'=>'物流公司代码','default'=>'','example'=>'SF', 'msg'=>'请选择物流公司!'],
            'logi_no' =>['type'=>'string','valid'=>'required', 'description'=>'运单号','default'=>'','example'=>'1', 'msg'=>'请输入运单号！'],
          //'tid' =>['type'=>'string','valid'=>'required', 'description'=>'订单号','default'=>'','example'=>'1'],
          //'post_fee' =>['type'=>'string','valid'=>'required', 'description'=>'运费','default'=>'','example'=>'10.00'],
            'memo' =>['type'=>'string','valid'=>'', 'description'=>'备注','default'=>'','example'=>'1'],
        );
        return $return;
    }

    public function update($params)
    {
        $deliveryFilter['delivery_id'] = $params['delivery_id'];
        if(!empty($params['shop_id']))
            $deliveryFilter['shop_id'] = $params['shop_id'];

        $corpInfo = app::get('syslogistics')->model('dlycorp')->getRow('corp_id,corp_name,corp_code', ['corp_id'=>$params['corp_id']]);


        $delivery = [];
        $delivery['corp_id']   = $params['corp_id'];
        $delivery['logi_name'] = $corpInfo['corp_name'];
        $delivery['corp_code'] = $corpInfo['corp_code'];
        $delivery['logi_no']   = $params['logi_no'];
        if(!empty($params['memo']))
            $delivery['memo'] = $params['demo'];

        $a = app::get('syslogistics')->model('delivery')->update($delivery, $deliveryFilter);

        if($a)
        {
            return true;
        }else{
            throw new LogicException('没有物流信息被更新！');
        }
    }
}
