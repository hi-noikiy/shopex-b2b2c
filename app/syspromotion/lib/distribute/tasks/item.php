<?php

class syspromotion_distribute_tasks_item extends base_task_abstract implements base_interface_task
{

    //params数据结构：
    // @param array user $params['user'];会员信息
    // @param array distribute $params['distribute'];推送的数据结构，详情查看dbschema/distribute
    public function exec($params = null)
    {

        $user       = $params['user'];
        $distribute = $params['distribute'];
        $params['detail'] = kernel::single('syspromotion_distribute_detail')->create($distribute, $user);
        return true;
    }

}


