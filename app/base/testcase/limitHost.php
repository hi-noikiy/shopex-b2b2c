<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class limitHost extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        //$this->model = app::get('base')->model('members');
    }

    public function testCheck(){
        ecos_check_license_hostname();

    }

}
