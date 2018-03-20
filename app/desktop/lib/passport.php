<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 商家会员,登录注册流程
 */
class desktop_passport {

    public function __construct()
    {
        $this->app = app::get('desktop');
        kernel::single('base_session')->start(); 

    }

    /**
	* 认证用户名密码以及验证码等
    *
	* @param array $usrdata 认证提示信息
    *
	* @return bool|int返回认证成功与否
	*/
    public function login($data)
    {
        $data = utils::_filter_input($data);

        $accountId = $this->__verifyLogin($data['uname'], $data['password']);

        pamAccount::setSession($accountId, trim($data['uname']));


        $this->__adminloginlog();//记录平台管理员登录日志

        return $rows['account_id'];

    }

    private function __verifyLogin($loginName, $password)
    {
        if( empty($loginName) || !$password )
        {
            pamAccount::setLoginErrorCount();
            throw new \LogicException(app::get('desktop')->_('用户名或密码错误'));
        }

        $rows = app::get('desktop')->model('account')->getRow('*',array('login_name'=>trim($loginName),'disabled' => 0) );

        if($rows && hash::check($password, $rows['login_password']))
        {
            pamAccount::setLoginErrorCount(true);
        }
        else
        {
            pamAccount::setLoginErrorCount();
            throw new \LogicException(app::get('desktop')->_('用户名或密码错误'));
        }

        return $rows['account_id'];
    }

    //管理员登录日志队列
    private function __adminloginlog()
    {
        $queue_params = array(
            'admin_userid'   => pamAccount::getAccountId(),
            'admin_username' => pamAccount::getLoginName(),
            'login_time'     => time(),
            'ip'             => request::getClientIp(), 
        );
        return system_queue::instance()->publish('system_tasks_adminloginlog', 'system_tasks_adminloginlog', $queue_params);
    }
}

