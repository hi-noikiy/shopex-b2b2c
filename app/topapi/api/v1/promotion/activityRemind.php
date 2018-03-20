<?php
/**
 * topapi
 *
 * -- promotion.activity.remind
 * -- 获取活动订阅信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_activityRemind implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '活动订阅信息';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'activity_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'1', 'desc'=>'活动id', 'msg'=>'活动id必须是正整数'],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $filter = array(
            'activity_id' => $params['activity_id'],
            'fields' => 'remind_time,activity_id,remind_way,remind_enabled,release_time,end_time,start_time',
        );
        $activitys = app::get('topapi')->rpcCall('promotion.activity.list', $filter);
        $pagedata['activity'] = $activitys['data'][0];
        if($pagedata['activity']['remind_way'] == "mobile")
        {
            $filter2 = ['user_id'=>$params['user_id'],'remind_way'=>'mobile','time_field' =>'add_time','bthan'=>strtotime(date('Y-m-d')),'sthan'=>strtotime(date('Y-m-d 23.59.59'))];
            $remind = app::get('topapi')->rpcCall('promotion.activity.remind.get', $filter2);
            $total = app::get('topapi')->rpcCall('promotion.setting');
            //$pagedata['remind'] = $remind;
            $pagedata['remind_total'] = $total;
            $pagedata['remind_residue'] = intval($total['mobile_num']-$remind['count']);
        }
        return $pagedata;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '';
    }

}
