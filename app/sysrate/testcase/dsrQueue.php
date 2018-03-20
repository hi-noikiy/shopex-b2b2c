<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class dsrQueue extends PHPUnit_Framework_TestCase {

    public function testDsrWorker()
    {
        $worker = kernel::single('sysrate_tasks_dsr');
        $worker->exec();

    }
}
