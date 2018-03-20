<?php

//这个类可以弃用了，因为使用了events
class syspromotion_distribute_tasks_distribute extends base_task_abstract implements base_interface_task
{

    //每次抽取用户只抽取100个
    var $limit = 100;

    public function exec($params = null)
    {

        logger::info(json_encode($params));
        return true;
    }

}


