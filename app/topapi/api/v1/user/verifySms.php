<?php
/**
 * topapi
 *
 * -- user.verifySms
 * -- 验证短信
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_verifySms implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '验证注册短信和忘记密码短信';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'mobile' => ['type'=>'string', 'valid'=>'required|mobile', 'example'=>'13918087654', 'desc'=>'手机号'],
            'type'   => ['type'=>'string', 'valid'=>'required|in:signup,forgot', 'desc'=>'验证发送短信类型signup注册发送短信 forgot忘记密码发送短信'],
            'vcode'  => ['type'=>'string', 'valid'=>'required', 'desc'=>'短信验证码', 'msg'=>'请输入验证码'],
        ];
    }

    /**
     * @return string verifySms_token 验证短信后返回的token，用于后续操作，注册或者找回密码重置登录密码
     */
    public function handle($params)
    {
        if( !userVcode::verify($params['vcode'], $params['mobile'], $params['type']) )
        {
            throw new \LogicException(app::get('topapi')->_('验证码输入错误'));
        }

        $signupToken = cache::store('vcode')->get('topapi'.$params['mobile'].$data['type']);
        $res['verifySms_token'] = $signupToken;

        return $res;
    }
}

