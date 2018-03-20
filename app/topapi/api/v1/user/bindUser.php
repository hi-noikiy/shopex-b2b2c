<?php
/**
 * topapi
 *
 * -- user.trust.bindUser
 * -- 信任登录绑定用户(dcloud)
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_user_bindUser implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '信任登录绑定用户';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'uname'        => ['type'=>'string', 'valid'=>'required', 'example'=>'', 'desc'=>'用户名', 'msg'=>'用户名必填'],
            'password'     => ['type'=>'string', 'valid'=>'required', 'example'=>'', 'desc'=>'密码', 'msg'=>'密码必填'],
            'password_confirm' => ['type'=>'string', 'valid'=>'required_if:option,new', 'example'=>'', 'desc'=>'确认密码', 'msg'=>'确认密码必填'],
            'deviceid'     => ['type'=>'string', 'valid'=>'required', 'example'=>'iphone 7', 'desc'=>'用户设备'],
            'option'       => ['type'=>'string', 'valid'=>'required|in:new,old', 'example'=>'new', 'desc'=>'绑定并注册新用户或者绑定已有用户'],
            'trust_params' => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'信任登录返回参数(dcloud)'],
            'vcodekey'     => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'验证码key'],
            'verifycode'   => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'验证码'],
        ];
    }

    /**
     * @return array params 入参
     * @return array 注册登录绑定后返回信任登录相关信息
     */
    public function handle($params)
    {
        $loginName       = $params['uname'];
        $password        = $params['password'];
        $passwordConfirm = $params['password_confirm'];
        $deviceid        = $params['deviceid'];
        $option          = $params['option'];
        $verifyCode      = $params['verifycode'];
        $verifyKey       = $params['vcodekey'];
        $trustparams     = json_decode($params['trust_params'], 1);
        $id              = $trustparams['id'];
        $authResult      = $trustparams['authResult'];
        $userInfo        = $trustparams['userInfo'];

        $showLoginVcode = ''; //是否显示登录验证码输入

        if($option=='new' && $password!=$passwordConfirm)
        {
           throw new \LogicException(app::get('topapi')->_('密码与确认密码不一致，请重新输入'));
        }

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

        if( $option=='new' || ($option=='old' && userAuth::isShowVcode('login')) ){
            if( (!$verifyKey) || empty($verifyCode) || !base_vcode::verify($verifyKey, $verifyCode) )
            {
                return [
                    'error'=>'验证码填写错误',
                    'showLoginVcode'=>'vcode_is_show',
                ];
                throw new \LogicException(app::get('topapi')->_('验证码填写错误'));
            }
        }

        try
        {
            $trustManager = kernel::single('sysuser_passport_trust_trust');
            if($option=='old')
            { // 已有用户
                $userId = app::get('topapi')->rpcCall('user.login', ['user_name' => $loginName, 'password' => $password]);
            }
            elseif($option=='new')
            { // 新注册用户
                $userId = userAuth::signUp($loginName, $password, $passwordConfirm);
            }

            //app端记录登录日志
            kernel::single('topapi_passport')->addLoginLog($userId,$loginName,$id);

            if ($userId)
            {
                // 插入绑定信息
                $ifbinded = $trustManager->bind($userId, $userFlag);

                if ($ifbinded)
                {
                    $user = app::get('topapi')->rpcCall('user.get.account.name', array('user_id'=>$userId));
                    $res = [
                        'binded' => 1,
                        'account' => $user[$userId],
                        'accessToken' => kernel::single('topapi_token')->make($userId, ['deviceid'=>$deviceid]),
                    ];
                }
                else
                {
                    $res = [
                        'binded' => 0,
                        'error' => app::get('topapi')->_('绑定用户失败'),
                    ];
                }
            }
        }
        catch (Exception $e)
        {
            if( $option=='old' && userAuth::isShowVcode('login') )
            {
                $showLoginVcode = 'vcode_is_show';
            }
            $res = [
                'binded' => 0,
                'error' => $e->getMessage(),
                'showLoginVcode' => $showLoginVcode,
            ];
        }

        return $res;
    }
}
