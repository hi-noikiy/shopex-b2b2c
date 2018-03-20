<?php

class syslogistics_api_ziti_listById {

    public $apiDescription = "根据自提ID，获取多条自提点信息";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'id' =>['type'=>'string','valid'=>'required', 'description'=>'自提点ID,多条逗号隔开','default'=>'','example'=>'10'],
        );

        return $return;
    }

    public function get($params)
    {
        $objMdlZiti = app::get('syslogistics')->model('ziti');
        $zitiId = explode(',',$params['id']);
        $data = $objMdlZiti->getList('*', ['id'=>$zitiId]);
        foreach( $data as &$row  )
        {
            $row['area_id'] = $row['area'];
            $row['area']    = area::getSelectArea($row['area'],'');
        }
        return $data;
    }
}

