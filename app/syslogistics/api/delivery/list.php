<?php

class syslogistics_api_delivery_list {

    public $apiDescription = "批量获取订单发货信息";

    public function getParams()
    {
        $return['params'] = array(
            'tid' =>['type'=>'string','valid'=>'required', 'description'=>'订单号，多个用逗号隔开','default'=>'','example'=>''],
        );
        return $return;
    }

    public function get($params)
    {
        $tid = explode(',', $params['tid']);
        $rows = 'logi_name,logi_no,corp_code,tid';
        $data = app::get('syslogistics')->model('delivery')->getList($rows, array('tid'=>$tid,'status'=>'succ'));
        if(!$data)
        {
            return false;
        }
        return $data;
    }
}
