<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class useCoupon extends PHPUnit_Framework_TestCase {

  //public function testSet()
  //{
  //    $userId = 3;
  //    $shopId = 5;
  //    $code = 'fewa2';
  //    kernel::single('systrade_cart_coupon_redis')->set($userId, $shopId, $code);
  //}

  //public function testGet()
  //{
  //     $userId = 3;
  //    $shopId = 4;
  //    $code = kernel::single('systrade_cart_coupon_redis')->get($userId, $shopId);
  //    var_dump($code);
  //}

  //public function testDel()
  //{
  //    $userId = 3;
  //    $shopId = 5;
  //    kernel::single('systrade_cart_coupon_redis')->del($userId, $shopId);
  //}

    public function testClean()
    {
        $userId = 3;
        kernel::single('systrade_cart_coupon_redis')->clean($userId);
    }




}
