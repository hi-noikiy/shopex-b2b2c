<?php

class sysitem_tasks_storeSyncDB extends base_task_abstract implements base_interface_task {

    public function exec($params=null)
    {
        kernel::single('sysitem_command_store')->command_storeSyncDB();
    }
}

