<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class scratchcard extends PHPUnit_Framework_TestCase
{
    public function setUp(){
    }

    public function testScratchcard()
    {
        /*** 测试获取刮刮卡数据
        $scratchcard = kernel::single('syspromotion_scratchcard_object')->getScratchcard(1, 4);
            var_dump($scratchcard);
            ****/

        /**** 测试抽奖内容
        $scratchcard = kernel::single('syspromotion_scratchcard_object')->receiveScratchcard(2, 5);
        var_dump($scratchcard);
         * **/

        /**** 测试奖品兑现
         * **/
        $scratchcard = kernel::single('syspromotion_scratchcard_object')->exchangeScratchcard(1);
        var_dump($scratchcard);

        /*****
        $scratchcard = kernel::single('syspromotion_scratchcard_object')->getScratchcard(1, 4);
        $times = kernel::single('syspromotion_scratchcard_object')->incr($scratchcard['scratchcard'], 4);

            var_dump($scratchcard);
            ****/
    }
}
