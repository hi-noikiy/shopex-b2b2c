<?php
// 只用于demo测试时使用，正式站不可以用
defined('DEV_CHECKDEMO') and DEV_CHECKDEMO or kernel::abort(404);
class toputil_ctl_passportdemo {

    /**
     * @brief 商家登录
     *
     * @return
     */
    public function login()
    {
        try
        {
            shopAuth::login(input::get('login_account'), input::get('login_password'));
        }
        catch(Exception $e)
        {
            $url = url::action('topshop_ctl_passport@signin');
            $msg = $e->getMessage();
        }

        if( pamAccount::check() )
        {

            $url = url::action('topshop_ctl_index@index');
            $msg = app::get('topshop')->_('登录成功');
            logger::info('demo站登录。账号是'.input::get('login_account'));
            return redirect::to($url);
        }
        else
        {
            return $this->splash('error',$url,$msg);
        }

    }

}
