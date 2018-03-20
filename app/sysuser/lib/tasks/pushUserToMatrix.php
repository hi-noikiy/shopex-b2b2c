<?php
class sysuser_tasks_pushUserToMatrix extends base_task_abstract implements base_interface_task{

    public function exec($params=null)
    {
        $userId = $params['userId'];
        $pusher = new sysuser_events_listeners_notifyShopexMatrix();
        $pusher->createUser($userId);

        return true;
    }
}
