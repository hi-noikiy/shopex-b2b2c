<?php


use Predis\Command\ScriptCommand;

class base_redis_scripts_migrateQueueJobs extends ScriptCommand
{
    public function getKeysCount()
    {
        // Tell Predis to use all the arguments but the last one as arguments
        // for KEYS. The last one will be used to populate ARGV.
        return -1;
    }

    public function getScript()
    {
        return kernel::single('ecos_lua')->getScriptMigrateQueueJobs();
    }
}

