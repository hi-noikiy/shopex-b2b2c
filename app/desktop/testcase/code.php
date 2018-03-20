<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class code extends PHPUnit_Framework_TestCase
{
    function setUp() {
    }

    public function testGetCode()
    {
        $code = kernel::single('desktop_code')->getCodeExpire();
        var_dump($code);
    }



}

