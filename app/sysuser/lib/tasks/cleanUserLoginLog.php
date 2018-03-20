<?php
class sysuser_tasks_cleanUserLoginLog extends base_task_abstract implements base_interface_task{
    // 清理会员登录日志，默认保存30天
    public function exec($params=null)
    {
        $day = (int)app::get('sysconf')->getConf('user.login.log.clean');
        $timespan = $day ? 3600*24*$day : 3600*24*30;
        $filter = ['login_time|lthan'=>time()-$timespan];
        app::get('sysuser')->model('user_login_log')->delete($filter);
    }
}
