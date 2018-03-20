<?php
class sysshop_tasks_cleansellerloginlog extends base_task_abstract implements base_interface_task{
    // 清理商家登录日志，默认保存30天
    public function exec($params=null)
    {
        $day = (int)app::get('sysconf')->getConf('shop.cleanloginlog.time');
        $timespan = $day ? 3600*24*$day : 3600*24*30;
        $filter = ['login_time|lthan'=>time()-$timespan];
        app::get('sysshop')->model('sellerloginlog')->delete($filter);
    }
}
