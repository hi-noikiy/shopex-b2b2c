<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class counter extends PHPUnit_Framework_TestCase
{
    public function setUp(){
        $this->testData = array(
            'scene' => 'syspromotion.scratchcard.limit',
            'userId' => rand(9000000, 9999999),
            'scratchcardId'=> rand(9000000, 9999999),
            'limit' => 10,
        );

    }

    public function testCounter()
    {
        echo "\ntestData:\n";
        var_dump($this->testData);
        echo "\n\n";
        $counter = syspromotion_counter::instance($this->testData['scene']);

        $times = $counter->readUserTimes($this->testData['scratchcardId'], $this->testData['userId'], $this->testData['limit']);
        echo "\ndefault times ： " . $times;
        $times = $counter->tryVerify($this->testData['scratchcardId'], $this->testData['userId'], $this->testData['limit']);
        echo "\ndecrement times ： " . $times;
        $times = $counter->addVerify($this->testData['scratchcardId'], $this->testData['userId'], $this->testData['limit']);
        echo "\nincrement times ： " . $times;


    }
}
