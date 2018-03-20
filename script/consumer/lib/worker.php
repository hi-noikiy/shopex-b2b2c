<?php
//-----------------------------------------------------------------------------------------------
//init ec-os framework start;
require_once(__DIr__ .'/../../lib/runtime.php');

//-----------------------------------------------------------------------------------------------
$topic = $_SERVER['argv'][1];
system_queue_worker::checkTopic($topic);

$worker = new system_queue_worker($topic);

$worker->run();

