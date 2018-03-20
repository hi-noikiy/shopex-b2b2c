<?php
class sysstat_api_trafficStat_get{
    public $apiDescription = "记录网站流量";

    public $use_starict_filter = true;//是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'inforType' => ['type'=>'string','valid'=>'required','description'=>'传入的类型 一共有2种（weball,item）'],
            'timeType' => ['type'=>'string','valid'=>'required','description'=>'传入的时间类型 一共有6种(yesterday,beforday,week,month,select)'],
            'starttime' => ['type'=>'string','valid'=>'', 'description'=>'起始时间段。如：2015/05/15-2015/05/15'],
            'endtime' => ['type'=>'string','valid'=>'', 'description'=>'结束时间段。如：2015/05/03-2015/05/03'],
            'limit' => ['type'=>'int','valid'=>'','description'=>'查询限制的条数'],
            'start' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'','description'=>'查询开始的条数'],
            'dataType' => ['type'=>'string','valid'=>'', 'description'=>'获取的数据类型'],
            'itemids' =>['type'=>'string','valid'=>'required_if:inforType,item', 'description'=>'商品id'],
        );
        return $return;
    }
    public function getData($params)
    {
        $storage = config::get('stat.default');
        return kernel::single('sysstat_traffic_adapter_'.$storage)->getData($params);
    }
}


