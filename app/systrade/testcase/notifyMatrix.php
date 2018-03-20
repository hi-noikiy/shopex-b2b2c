<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class notifyMatrix extends PHPUnit_Framework_TestCase {

    public function setup()
    {
        $this->tradeList = [
            'trade' => [
                [
                    'tid' => '1610140231290044',
                    'shop_id' => '3',
                ],
            ],
        ];
    }

    public function testNotifyMatrixByPlatform()
    {
        $listener = new systrade_events_listeners_notifyShopexMatrixByPlatform();

        $res = $listener->tradeCreate($this->tradeList, []);

        var_dump($res);
    }
}

