<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topc_ctl_member_deposit extends topc_ctl_member{

    //修改支付密码之输入登录密码页面
    public function modifyPasswordCheckLoginPassword()
    {
        $this->action_view = "deposit/modifyPasswordCheckLoginPassword.html";
        return $this->output($pagedata);
    }

    //修改支付密码之判断登录密码
    public function doModifyPasswordCheckLoginPassword()
    {
        $password = input::get('password');
        try{
            $resutl = app::get('topc')->rpcCall('user.login.pwd.check', ['password'=> $password], 'buyer');
            $this->setSessionValue('setDepositPasswordFlagCheckLogin', true);
        }
        catch(Exception $e)
        {
            return $this->splash('error', null, $e->getMessage());
        }
        $url = url::action('topc_ctl_member_deposit@modifyPassword');
        return $this->splash('succ', $url, '验证成功');
    }

    //修改支付密码之修改页面
    public function modifyPassword()
    {


        $userId = userAuth::id();
        $depositPasswordFlag = app::get('topc')->rpcCall('user.deposit.password.has', ['user_id'=>$userId]);
        $depositPasswordFlag = $depositPasswordFlag['result'];
        if(!$depositPasswordFlag)
        {
            $setDepositPasswordFlagCheckLogin = $this->getSessionValue('setDepositPasswordFlagCheckLogin', false);
            if(!$setDepositPasswordFlagCheckLogin)
                return redirect::action('topc_ctl_member_deposit@modifyPasswordCheckLoginPassword');
        }

        $pagedata['hasDepositPassword'] = $depositPasswordFlag;
        $this->action_view = "deposit/modifyPassword.html";
        return $this->output($pagedata);
    }

    //修改支付密码之保存动作
    public function doModifyPassword()
    {
        try
        {
            $userId = userAuth::id();
            $depositPasswordFlag = app::get('topc')->rpcCall('user.deposit.password.has', ['user_id'=>$userId]);
            $depositPasswordFlag = $depositPasswordFlag['result'];


            $oldPassword = input::get('old_password');
            $newPassword = input::get('new_password');
            $confirm_password = input::get('confirm_password');

            if($newPassword != $confirm_password)
                throw new LogicException(app::get('topc')->_('两次输入密码不一致！请确认'));

            $this->checkPassword($newPassword);

            // 生成跳转url，判断是否有支付单号
            $paymentId = cache::store('session')->pull($this->cachePaymentIdKey.'-'.$userId);
            if($paymentId)
            {
                $returnUrl = url::action('topc_ctl_paycenter@index', ['payment_id' => $paymentId]);
            }
            else
            {
                $returnUrl = url::action('topc_ctl_member@security');
            }

            if($depositPasswordFlag)
            {
                $requestParams = ['user_id'=>$userId, 'old_password'=>$oldPassword, 'new_password'=>$newPassword];
                app::get('topc')->rpcCall('user.deposit.password.change', $requestParams);

            }
            else
            {
                $setDepositPasswordFlagCheckLogin = $this->getSessionValue('setDepositPasswordFlagCheckLogin', false);
                if(!$setDepositPasswordFlagCheckLogin)
                    throw new LogicException(app::get('topc')->_('登陆密码验证已失效，请到安全中心重新设置支付密码'));

                $requestParams = ['user_id'=>$userId, 'password'=>$newPassword];
                app::get('topc')->rpcCall('user.deposit.password.set', $requestParams);
                $this->setSessionValue('setDepositPasswordFlagCheckLogin', false);
            }
            return $this->splash('succ', $returnUrl, '保存成功');
        }
        catch(Exception $e)
        {
            return $this->splash('error', null, $e->getMessage());
        }

        return redirect::action('topc_ctl_member@security');
    }

    //忘记支付密码之找回支付密码页面
    public function forgetPassword()
    {
        $userId = userAuth::id();
        //会员信息
        $data = userAuth::getUserInfo();
        if( (!empty($data['email']) && $data['email_verify']) || !empty($data['mobile']))
        {
            $send_status = 'true';
        }
        else
        {
            $send_status = 'false';
        }
        $pagedata['send_status'] = $send_status;
        $pagedata['data'] = $data;


        return $this->page('topc/member/deposit/forgetPassword.html', $pagedata);
    }

    //忘记支付密码之设置云存款密码页面
    public function forgetPasswordSetPassword()
    {
        $postData = input::get();
        $vcode = $postData['vcode'];
        $loginName = $postData['uname'];
        $sendType = $postData['type'];
        $response_json = $postData['response_json'];
        try
        {
            $vcodeData=userVcode::verify($vcode,$loginName,$sendType);
            if(!$vcodeData)
            {
                throw new LogicException('验证码错误');
            }
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
            return $this->splash('error',null,$message);
        }

        $this->setSessionValue('setDepositPasswordFlag', true);
        if($response_json == 'true')
        {
            return view::make('topc/member/deposit/forgetPasswordSetPasswordJson.html', $pagedata);
        }
        return $this->page('topc/member/deposit/forgetPasswordSetPassword.html', $pagedata);
    }

    //忘记支付密码之修改的密码保存动作
    public function forgetPasswordFinished()
    {

        try{

            $flag = $this->getSessionValue('setDepositPasswordFlag', false);
            if($flag)
            {
                $userId = userAuth::id();
                $postData = input::get();
                $newPassword = $postData['password'];
                $confirmPassword = $postData['confirmpwd'];

                $validator = validator::make(
                    ['password' => $postData['password'] , 'password_confirmation' => $postData['confirmpwd']],
                    ['password' => 'min:6|max:20|confirmed'],
                    ['password' => '密码长度不能小于6位!|密码长度不能大于20位!|输入的密码不一致!']
                );
                if ($validator->fails())
                {
                    $messages = $validator->messagesInfo();
                    foreach( $messages as $error )
                    {
                        throw new LogicException($error[0]);
                    }
                }

                $this->checkPassword($newPassword);

                //请求接口修改密码
                $requestParams = ['user_id'=>$userId, 'password'=>$newPassword];
                app::get('topc')->rpcCall('user.deposit.password.set', $requestParams);

                $this->setSessionValue('setDepositPasswordFlag', false);
                return view::make('topc/member/deposit/forgetPasswordFinished.html', $pagedata);
            }
            else
            {
                throw new LogicException('忘记密码链接已经过期，请重新发起');
            }
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
            return $this->splash('error',null,$message, 1);
        }

    }

    //忘记支付密码的时候发送验证码
    public function forgetPasswordSendVcode()
    {

        $postData = utils::_filter_input(input::get());
        $validator = validator::make(
            [$postData['uname']],['required'],['您的邮箱或手机号不能为空!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }


        $accountType = app::get('topc')->rpcCall('user.get.account.type',array('user_name'=>$postData['uname']),'buyer');

        if($accountType == "mobile")
        {
            $valid = validator::make(
                [$postData['verifycode']],['required']
            );
            if($valid->fails())
            {
                return $this->splash('error',null,"图片验证码不能为空!");
            }
            if(!base_vcode::verify($postData['verifycodekey'],$postData['verifycode']))
            {
                return $this->splash('error',null,"图片验证码错误!");
            }
        }

        try
        {
            $this->passport->sendVcode($postData['uname'],$postData['type']);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
        }

        if($accountType == "email")
        {
            return $this->splash('success',null,"邮箱验证链接已经发送至邮箱，请登录邮箱验证");
        }
        else
        {
            return $this->splash('success',null,"验证码发送成功");
        }
    }

    //一个session的写入抽象
    private function setSessionValue($key, $value)
    {
        kernel::single('base_session')->start();
        $_SESSION[$key] = $value;
        kernel::single('base_session')->close();
        return true;
    }

    //一个session的获取抽象
    private function getSessionValue($key, $default)
    {
        kernel::single('base_session')->start();
        $value = $_SESSION[$key];
        kernel::single('base_session')->close();
        return $value ? $value : $default;
    }

    //验证支付密码复杂度
    private function checkPassword($newPassword)
    {
        $a = 0;
        if(preg_match("/(?=.*[0-9])[a-zA-Z0-9]{6,20}/", $newPassword))
            $a += 1;
        if(preg_match("/(?=.*[a-z])[a-zA-Z0-9]{6,20}/", $newPassword))
            $a += 1;
        if(preg_match("/(?=.*[A-Z])[a-zA-Z0-9]{6,20}/", $newPassword))
            $a += 1;

        if($a >= 2)
            return true;

        throw new LogicException('6-20个字符，不能与登录密码一致，至少包含数字、大写英文、小写英文中的两种。');
    }

}
