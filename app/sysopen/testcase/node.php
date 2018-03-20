<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class node extends PHPUnit_Framework_TestCase
{
    public function setUp(){
    }

    public function testNodeInfo(){
        $nodeInfo = sysopen_shopexnode::getNodeInfo();
        echo "\n";
        echo "========= nodeInfo ==============";
        echo "\n";
        var_dump($nodeInfo);
        echo "\n";
        echo "=================================";
        echo "\n";
    }


    public function testShowBind(){
        $url = kernel::single('sysopen_shopex_bind')->showBind();
        echo "\n";
        echo "====== show bind url ============";
        echo "\n";
        var_dump($url);
        echo "\n";
        echo "=================================";
        echo "\n";
    }

}
