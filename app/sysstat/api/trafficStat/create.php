<?php
class sysstat_api_trafficStat_create{
    public $apiDescription = "记录网站流量";

    public $use_starict_filter = true;//是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'page' => ['type'=>'string','valid'=>'required','description'=>'页面标识'],
            'page_rel_id' => ['type'=>'int','valid'=>'required','description'=>'页面关联id'],
            'use_platform' => ['type'=>'string','valid'=>'required','description'=>'页面id'],
            'shop_id' => ['type'=>'int','valid'=>'required', 'description'=>'店铺id'],
            'remote_addr' =>['type'=>'string','valid'=>'required','description'=>'远程ip'],
        );
        return $return;
    }
    public function create($params)
    {
        $storage = config::get('stat.default');
        return kernel::single('sysstat_traffic_adapter_'.$storage)->save($params);
    }
}


