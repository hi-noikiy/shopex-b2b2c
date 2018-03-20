<?php
/**
 * topapi
 *
 * -- user.login
 * -- 用户登录
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v2_user_login implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户登录';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'account'  => ['type'=>'string', 'valid'=>'required', 'example'=>'demo',    'desc'=>'登录账号/手机/邮箱', 'msg'=>'请填写登录账号'],
            'password' => ['type'=>'string', 'valid'=>'required', 'example'=>'demo123', 'desc'=>'登录密码',         'msg'=>'请填写密码'],
            'deviceid' => ['type'=>'string', 'valid'=>'required', 'example'=>'demo123', 'desc'=>'用户设备'],
            'clientid' => ['type'=>'string', 'valid'=>'required', 'example'=>'demo123', 'desc'=>'用户设备id'],
            'token' => ['type'=>'string', 'valid'=>'', 'example'=>'demo123', 'desc'=>'用户设备token'],
            'type' => ['type'=>'string', 'valid'=>'required|in:ios,android', 'example'=>'demo123', 'desc'=>'用户设备类型，安卓还是苹果'],
            'plugin' => ['type'=>'string', 'valid'=>'required|in:igexin,mipush', 'title'=>'终端类型', 'example'=>'', 'desc'=>'插件类型,个推还是小米推送'],
            //'vcode'    => ['type'=>'string', 'valid'=>'',         'example'=>'',        'desc'=>'登录超过3次，出现图片验证码，需要验证图片验证码,图形验证码类型为topapi_login', 'msg'=>'请输入验证码'],
        ];
    }

    /**
     * @return int userId 用户ID
     * @return string accessToken 注册完成后返回的accessToken
     */
    public function handle($params)
    {
        $account = $params['account'];
        $password = $params['password'];

        $ErrorCountKey = 'topapi_login_error_count'.$params['account'];

        $ErrorCount = cache::store('vcode')->get($ErrorCountKey);
        if( $ErrorCount >= 3 )
        {
            ////验证图形验证码
            //if( !$params['vcode'] || !base_vcode::verify('topapi_login', $params['vcode']))
            //{
            //    throw new \LogicException(app::get('topapi')->_('验证码填写错误'));
            //}
        }

        try
        {
            $result['user_id'] = app::get('topapi')->rpcCall('user.login', ['user_name' => $account, 'password' => $password]);

            //app端记录登录日志
            kernel::single('topapi_passport')->addLoginLog($result['user_id'],$account);

            cache::store('vcode')->put($ErrorCountKey,0);
        }
        catch( Exception $e )
        {
            cache::store('vcode')->put($ErrorCountKey,$ErrorCount+1, 86400);
            throw $e;
        }

        $data['account'] = $account;
        $data['password'] = $password;
        $data['deviceid'] = $params['deviceid'];
        $result['accessToken'] = kernel::single('topapi_token')->make($result['user_id'], $data);

        $clientInfoParams['clientid'] = $params['clientid'];
        $clientInfoParams['user_id'] = $result['user_id'];
        $clientInfoParams['token'] = $params['token'];
        $clientInfoParams['type'] = $params['type'];
        $clientInfoParams['plugin'] = $params['plugin'];
        $ret = app::get('topapi')->rpcCall(
            'sysapp.push.login',
            $clientInfoParams
       );


        return $result;
    }


}

