<?php
class system_tasks_cleansmslog extends base_task_abstract implements base_interface_task{
    // 清理短信日志，默认保存30天
    public function exec($params=null)
    {
        $day = (int)app::get('sysconf')->getConf('sms.cleanlog.time');
        $timespan = $day ? 3600*24*$day : 3600*24*30;
        $filter = ['send_time|lthan'=>time()-$timespan];
        app::get('system')->model('smslog')->delete($filter);
    }
}
