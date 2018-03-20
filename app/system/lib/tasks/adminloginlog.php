<?php
class system_tasks_adminloginlog extends base_task_abstract implements base_interface_task{
    public function exec($params=null)
    {
        $queue_params = array(
            'admin_userid'   => $params['admin_userid'],
            'admin_username' => $params['admin_username'],
            'login_time'     => $params['login_time'],
            'ip'             => $params['ip'],
        );
        app::get('system')->model('adminloginlog')->insert($params); 
    }
}


