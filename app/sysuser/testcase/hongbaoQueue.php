<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class hongbaoQueue extends PHPUnit_Framework_TestCase
{
    public function setUp(){
    }

    public function testWorker()
    {
        $worker = kernel::single('sysuser_tasks_hognbaoExpired');
        $worker->exec();
    }

}
