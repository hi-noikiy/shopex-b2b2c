<?php
/**
 * topapi
 *
 * -- user.signup
 * -- 用户注册
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_signup implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户注册';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'account'               => ['type'=>'string', 'valid'=>'required',               'example'=>'demo',   'desc'=>'注册账号'],
            'password'              => ['type'=>'string', 'valid'=>'min:6|max:20|confirmed', 'example'=>'demo123', 'desc'=>'登录密码',   'msg'=>'密码长度不能小于6位|密码长度不能大于20位|输入的密码不一致'],
            'password_confirmation' => ['type'=>'string', 'valid'=>'required',               'example'=>'demo123', 'desc'=>'确认密码',   'msg'=>'请填写确认密码'],
            'deviceid'              => ['type'=>'string', 'valid'=>'required',               'example'=>'Xebsweb', 'desc'=>'用户设备'],
            'signup_token'          => ['type'=>'string', 'valid'=>'required',               'example'=>'',        'desc'=>'注册token'],
        ];
    }

    /**
     * @return int userId 用户ID
     * @return string accessToken 注册完成后返回的accessToken
     * @return string open_sendpoint 是否开启注册送积分（0未开启，1开启）
     * @return string sendPointNum 注册送积分数量
     */
    public function handle($params)
    {
        $signupToken = cache::store('vcode')->get('topapi'.$params['account'].'signup');
        if( $params['signup_token'] != $signupToken )
        {
            throw new \LogicException(app::get('topapi')->_('页面已过期'));
        }

        cache::store('vcode')->put('topapi'.$params['account'].'signup', false);

        $result['user_id'] = userAuth::signUp($params['account'], $params['password'], $params['password_confirmation']);

        //app端记录登录日志
        kernel::single('topapi_passport')->addLoginLog($result['user_id'],$params['account']);

        $data['account'] = $params['account'];
        $data['password'] = $params['password'];
        $data['deviceid'] = $params['deviceid'];
        $result['accessToken'] = kernel::single('topapi_token')->make($result['user_id'], $data);

        $openSendpoint = app::get('sysconf')->getConf('open.sendPoint');
        if( $openSendpoint == '1' )
        {
            $result['sendPointNum'] = app::get('sysconf')->getConf('sendPoint.num');
        }

        return $result;
    }
}

