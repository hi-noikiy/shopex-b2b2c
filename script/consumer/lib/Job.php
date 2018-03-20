<?php
class Job{

    private $topic = '';
    private $server = '';

    public function __construct($topic, $consumeConfig, &$server)
    {
        $this->topic = $topic;
        $this->queueServer = system_queue::instance();
        $this->maxRunTimes = $consumeConfig['maxRunTimes'];
        $this->server = $server;
        logger::debug("init job with: topic-$topic");
    }

    public function run($process)
    {
        $bin = $_SERVER['_'];
        $file = __DIR__ . '/worker.php';

        $process->exec($bin, [$file, $this->topic]);

        $pid = $process->pid;
        logger::debug("one process is over : $process->pid .");
        $process->close();
        $process->exit(0);
    }

}

