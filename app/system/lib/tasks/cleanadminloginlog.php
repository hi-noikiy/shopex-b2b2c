<?php
class system_tasks_cleanadminloginlog extends base_task_abstract implements base_interface_task{
    // 清理平台操作日志，默认保存30天
    public function exec($params=null)
    {
        $day = (int)app::get('sysconf')->getConf('admin.cleanlog.time');
        $timespan = $day ? 3600*24*$day : 3600*24*30;
        $filter = ['login_time|lthan'=>time()-$timespan];
        app::get('system')->model('adminloginlog')->delete($filter);
    }
}
