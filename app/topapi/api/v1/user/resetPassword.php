<?php
/**
 * topapi
 *
 * -- user.forgot.resetpassword
 * -- 验证短信
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_resetPassword implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '找回密码重新设置密码';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'mobile' => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918087654', 'desc'=>'手机号'],
            'forgot_token'   => ['type'=>'string', 'valid'=>'required', 'desc'=>'验证短信后返回的verifySms_token'],
            'password'              => ['type'=>'string', 'valid'=>'min:6|max:20|confirmed', 'example'=>'demo123', 'desc'=>'登录密码',   'msg'=>'密码长度不能小于6位|密码长度不能大于20位|输入的密码不一致'],
            'password_confirmation' => ['type'=>'string', 'valid'=>'required',               'example'=>'demo123', 'desc'=>'确认密码',   'msg'=>'请填写确认密码'],
        ];
    }

    /**
     * @return string verifySms_token 验证短信后返回的token，用于后续操作，注册或者找回密码重置登录密码
     */
    public function handle($params)
    {
        $token = cache::store('vcode')->get('topapi'.$params['mobile'].'forgot');
        if( $params['forgot_token'] != $token )
        {
            throw new \LogicException(app::get('topapi')->_('页面已过期'));
        }

        $userData = userAuth::getAccountInfo($params['mobile']);

        $data['type'] = 'reset';
        $data['new_pwd'] = $params['password'];
        $data['confirm_pwd'] = $params['password_confirmation'];
        $data['user_id'] = $userData['user_id'];

        app::get('topwap')->rpcCall('user.pwd.update',$data);

        kernel::single('topapi_token')->deleteUser($userData['userid']);

        return true;
    }
}

