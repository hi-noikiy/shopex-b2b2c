<?php
// 插入商家登录日志
class sysshop_tasks_sellerloginlog extends base_task_abstract implements base_interface_task{
    public function exec($params=null)
    {
        $queue_params = array(
            'seller_userid'   => $params['seller_userid'],
            'seller_username' => $params['seller_username'],
            'shop_id'         => $params['shop_id'],
            'login_time'    => $params['login_time'],
            'ip'              => $params['ip'],
        );
        app::get('sysshop')->model('sellerloginlog')->insert($queue_params);
    }
}


