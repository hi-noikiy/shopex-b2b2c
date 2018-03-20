<?php
class sysuser_ctl_admin_loginlog extends desktop_controller{
    public $workground = 'sysuser.wrokground.user';

    public function index()
    {
        $mdl = app::get('sysuser')->model('user_login_log');
        return $this->finder('sysuser_mdl_user_login_log',array(
            'title' => app::get('sysuser')->_('会员登录日志列表'),
        ));
    }

}
