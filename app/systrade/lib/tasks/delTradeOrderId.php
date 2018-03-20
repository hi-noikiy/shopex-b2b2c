<?php

class systrade_tasks_delTradeOrderId extends base_task_abstract implements base_interface_task
{
    public function exec($params=null)
    {
        $redis = redis::scene('tradeOrderId');
        $hkey = date('Ymd')-1;

        $keys = $redis->HKEYS($hkey);
        foreach( $keys as $key )
        {
            $redis->HDEL($hkey, $key);
        }

        return true;
    }
}
