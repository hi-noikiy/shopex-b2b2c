<?php
/**
 * topapi
 *
 * -- user.trust.login
 * -- 信任登录信息验证(dcloud)
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_dcloudtrustlogin implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '信任登录信息验证(dcloud)';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'trust_params'  => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'信任登录返回参数(dcloud)'],
            'deviceid'      => ['type'=>'string', 'valid'=>'', 'example'=>'iphone 7', 'desc'=>'用户设备'],
        ];
    }

    /**
     * @return int userId 用户ID
     * @return string accessToken 注册完成后返回的accessToken
     */
    public function handle($params)
    {
        $trustparams = json_decode($params['trust_params'], 1);
        $id         = $trustparams['id'];
        $authResult = $trustparams['authResult'];
        $userInfo   = $trustparams['userInfo'];
        $deviceid   = $params['deviceid'];

        if($id=='weixin')
        {
            $userFlag = md5($authResult['openid']);
            $openid = $authResult['openid'];
        }
        elseif($id=='qq')
        {
            $userFlag = substr(md5($authResult['openid']),1,10).'qq';
            $openid = $authResult['openid'];
        }
        elseif($id='sinaweibo')
        {
            $userFlag = substr(md5($authResult['uid']),1,10).'weibo';
            $openid = $authResult['uid'];
        }
        else
        {
            throw new \LogicException(app::get('topapi')->_('第三方信任登录信息有误'));
        }

        if(!$openid)
        {
            throw new \LogicException(app::get('topapi')->_('第三方信任登录信息有误'));
        }

        $trustManager = kernel::single('sysuser_passport_trust_trust');

        if ($userId = $trustManager->binded($userFlag))
        {
            $user = app::get('topapi')->rpcCall('user.get.account.name', array('user_id'=>$userId));
            $res = [
                'binded' => 1,
                'account' => $user[$userId],
                'accessToken' => kernel::single('topapi_token')->make($userId, ['deviceid'=>$deviceid]),
            ];

            //app端记录登录日志
            kernel::single('topapi_passport')->addLoginLog($userId,$user[$userId],$id);
        }
        else
        {
            $res = [
                'binded' => 0,
                'user_info' => $userInfo,
            ];
        }
        return $res;
    }
}

