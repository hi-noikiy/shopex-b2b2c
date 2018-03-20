<?php
/**
 * topapi
 *
 * -- promotion.activity.remindsubmit
 * -- 订阅活动提醒
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_activityRemindSubmit implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '订阅活动提醒';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'activity_id' => ['type'=>'int','valid'=>'required|min:1', 'example'=>'1', 'desc'=>'活动id', 'msg'=>'活动id必须是正整数'],
            'mobile'      => ['type'=>'string','valid'=>'required_without:email|mobile', 'example'=>'', 'desc'=>'手机', 'msg'=>"手机号必填|请填写正确的手机号"],
            'email'       => ['type'=>'string','valid'=>'required_without:mobile|email', 'example'=>'', 'desc'=>'邮箱', 'msg'=>"邮箱必填|请填写正确的邮箱"],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $uId = $params['user_id'];
        $filter = ['user_id'=>$uId,'activity_id' => $params['activity_id']];
        $remind = app::get('topapi')->rpcCall('promotion.activity.remind.get', $filter);
        $list = $remind['list'];
        if($remind['list'])
        {
            foreach($remind['list'] as $list)
            {
                if(isset($params['mobile']) && $list['remind_way'] == "mobile" && $list['remind_goal'] == $params['mobile'])
                {
                    throw new \LogicException('该手机号已订阅该活动');
                }
                if (isset($params['email']) && $list['remind_way'] == "email" && $list['remind_goal'] == $params['email'])
                {
                    throw new \LogicException('该邮箱已订阅该活动');
                }
            }
        }
        $filter2['platform'] = 'app';
        $filter2['user_id'] = $uId;
        $filter2['mobile'] = $params['mobile'];
        $filter2['email'] = $params['email'];
        $filter2['activity_id'] = $params['activity_id'];
        $filter2['url'] = url::action('topwap_ctl_activity@detail', array('id'=>$params['activity_id']));

        app::get('topapi')->rpcCall('promotion.activity.remind.add',$filter2);

        return true;
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
