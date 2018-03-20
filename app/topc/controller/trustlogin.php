<?php
class topc_ctl_trustlogin extends topc_controller{

    public function __construct()
    {
        parent::__construct();
        $this->setLayoutFlag('passport');
        kernel::single('base_session')->start();
        $this->passport = kernel::single('topc_passport');
    }

    /**
     * callback返回页, 同时是bind页面
     *
     * @return base_http_response
     */
    public function callback()
    {
        $params = input::get();
        $flag = $params['flag'];
        unset($params['flag']);

        // 信任登陆校验
        $userTrust = kernel::single('pam_trust_user');
        $res = $userTrust->authorize($flag, 'web', 'topc_ctl_trustlogin@callBack', $params);

        $binded = $res['binded'];
        $userinfo = $res['user_info'];
        $realname = $userinfo['nickname'];
        $avatar = $userinfo['figureurl'];

        if ($binded)
        {
            $userId = $res['user_id'];

            userAuth::login($userId,null,'pc',$flag);

            return redirect::action('topc_ctl_default@index');
        }
        else
        {
            if($flag=='qq')
            {
                $pagedata['realname'] =  $realname;
                $pagedata['avatar'] = $avatar;
            }
            $pagedata['flag'] = $flag;
            return $this->page('topc/bind.html', $pagedata);
        }
    }

    // public function bindDefaultCreateUser()
    // {
    //     $params = input::get();
    //     $flag = $params['flag'];
    //     try
    //     {
    //         $userId = kernel::single('pam_trust_user')->bindDefaultCreateUser($flag);
    //         userAuth::login($userId, $loginName);
    //         $url = url::action('topc_ctl_default@index');;
    //         return $this->splash('success', $url, $msg, true);

    //     }
    //     catch (\Exception $e)
    //     {
    //         $msg = $e->getMessage();
    //         return $this->splash('error',null,$msg,true);
    //     }
    // }

    public function bindExistsUser()
    {
        $params = input::get();
        $verifyCode = $params['verifycode'];
        $verifyKey = $params['vcodekey'];
        $loginName = $params['uname'];
        $password = $params['password'];

        if( (!$verifyKey) || $b=empty($verifyCode) || $c=!base_vcode::verify($verifyKey, $verifyCode))
        {
            $msg = app::get('topc')->_('验证码填写错误') ;
            return $this->splash('error', null, $msg, true);
        }

        try
        {
            if (userAuth::attempt($loginName, $password))
            {
                kernel::single('pam_trust_user')->bind(userAuth::id());
                $url = url::action('topc_ctl_default@index');
                return $this->splash('success', $url, $msg, true);
            }
        }
        catch (Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
    }

    public function bindSignupUser()
    {
        $params = input::get();
        $verifyCode = $params['verifycode'];
        $verifyKey =  $params['vcodekey'];
        $loginName = $params['pam_account']['login_name'];
        $password = $params['pam_account']['login_password'];
        $confirmedPassword = $params['pam_account']['psw_confirm'];

        // 用户名、手机号、邮箱本来都可以注册，这里只从源头定为只支持手机号注册
        // 如果还需要其他注册方式，则在后台开启支持多方式注册
        // 增加客户手机信息的留存
        if( !app::get('sysconf')->getConf('user.account.register.multipletype') )
        {
            if(!preg_match("/^1[34578]{1}[0-9]{9}$/", $loginName))
            {
                $msg = app::get('topc')->_("请输入正确的手机号码");
                return $this->splash('error', null, $msg, true);
            }
        }

        if( !$verifyKey || empty($verifyCode) || !base_vcode::verify($verifyKey, $verifyCode))
        {
            $msg = app::get('topc')->_('验证码填写错误') ;
            return $this->splash('error', null, $msg, true);
        }

        try
        {
            $userId = userAuth::signUp($loginName, $password, $confirmedPassword);
            userAuth::login($userId, $loginName,'pc',$params['flag']);

            kernel::single('pam_trust_user')->bind(userAuth::id());

            $url = url::action('topc_ctl_default@index');
            return $this->splash('success', $url, $msg, true);
        }
        catch (\Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }
    }
}
