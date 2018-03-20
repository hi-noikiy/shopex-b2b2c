<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topm_ctl_member_deposit extends topm_ctl_member{

    public function modifyPasswordCheckLoginPassword()
    {
        return $this->page('topm/member/deposit/modifyDepositPasswordCheckLogin.html');
    }

    public function doModifyPasswordCheckLoginPassword()
    {
        $password = input::get('password');
        try{
            $resutl = app::get('topm')->rpcCall('user.login.pwd.check', ['password'=> $password], 'buyer');
            $this->setSessionValue('setDepositPasswordFlagCheckLogin', true);
        }
        catch(Exception $e)
        {
            return $this->splash('error', null, $e->getMessage());

        }
        return $this->splash('succ', url::action('topm_ctl_member_deposit@modifyPassword'), '验证成功');
    }

    public function modifyPassword()
    {


        $userId = userAuth::id();
        $depositPasswordFlag = app::get('topm')->rpcCall('user.deposit.password.has', ['user_id'=>$userId]);
        $depositPasswordFlag = $depositPasswordFlag['result'];
        if(!$depositPasswordFlag)
        {
            $setDepositPasswordFlagCheckLogin = $this->getSessionValue('setDepositPasswordFlagCheckLogin', false);
            if(!$setDepositPasswordFlagCheckLogin)
                return redirect::action('topm_ctl_member_deposit@modifyPasswordCheckLoginPassword');
        }

        $pagedata['hasDepositPassword'] = $depositPasswordFlag;
        return $this->page("topm/member/deposit/modifyDepositPassword.html", $pagedata);
    }

    public function doModifyPassword()
    {
        try
        {
            $userId = userAuth::id();
            $depositPasswordFlag = app::get('topm')->rpcCall('user.deposit.password.has', ['user_id'=>$userId]);
            $depositPasswordFlag = $depositPasswordFlag['result'];


            $oldPassword = input::get('old_password');
            $newPassword = input::get('new_password');
            $confirm_password = input::get('confirm_password');

            if($newPassword != $confirm_password)
                throw new LogicException(app::get('topm')->_('两次输入密码不一致！请确认'));

            $this->checkPassword($newPassword);


            $returnUrl = url::action('topm_ctl_member@security');
            if($depositPasswordFlag)
            {
                $requestParams = ['user_id'=>$userId, 'old_password'=>$oldPassword, 'new_password'=>$newPassword];
                app::get('topm')->rpcCall('user.deposit.password.change', $requestParams);

            }
            else
            {
                $setDepositPasswordFlagCheckLogin = $this->getSessionValue('setDepositPasswordFlagCheckLogin', false);
                if(!$setDepositPasswordFlagCheckLogin)
                    throw new LogicException(app::get('topm')->_('登陆密码验证已失效，请到安全中心重新设置支付密码'));

                $requestParams = ['user_id'=>$userId, 'password'=>$newPassword];
                app::get('topm')->rpcCall('user.deposit.password.set', $requestParams);
                $this->setSessionValue('setDepositPasswordFlagCheckLogin', false);
            }
            return $this->splash('succ', $returnUrl, '保存成功');
        }
        catch(Exception $e)
        {
            return $this->splash('error', null, $e->getMessage());
        }

        return redirect::action('topm_ctl_member@security');
    }


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


        return $this->page('topm/member/deposit/forgetPassword.html', $pagedata);
    }

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
            return view::make('topm/member/deposit/forgetPasswordSetPasswordJson.html', $pagedata);
        }
        return $this->page('topm/member/deposit/forgetPasswordSetPassword.html', $pagedata);
    }

    public function forgetPasswordFinished()
    {

        try{

            $flag = $this->getSessionValue('setDepositPasswordFlag', false);
          //var_dump($flag);exit;
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
                $returnUrl = url::action('topm_ctl_member@security');
                return $this->splash('success', $returnUrl,app::get('topm')->_('密码修改成功!'), 1);
                //return $this->page('topm/member/deposit/forgetPasswordFinished.html', $pagedata);
            }
            else
            {
                throw new LogicException('忘记密码链接已经过期，请重新发起');
            }
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
            return $this->splash('error',null,$message, 0);
        }

    }

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


        $accountType = app::get('topm')->rpcCall('user.get.account.type',array('user_name'=>$postData['uname']),'buyer');

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

    private function setSessionValue($key, $value)
    {
        kernel::single('base_session')->start();
        $_SESSION[$key] = $value;
        kernel::single('base_session')->close();
        return true;
    }

    private function getSessionValue($key, $default)
    {
        kernel::single('base_session')->start();
        $value = $_SESSION[$key];
        kernel::single('base_session')->close();
        return $value ? $value : $default;
    }

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
