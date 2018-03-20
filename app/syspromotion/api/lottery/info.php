<?php
/**
 * ShopEx licence
 *
 * - promotion.lottery.get
 * - 获取单个转盘抽奖详情
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 *
 */
final class syspromotion_api_lottery_info {

    public $apiDescription = '获取单个转盘活动详情';

    public function getParams()
    {
        $return['params'] = array(
            'lottery_id' => ['type'=>'int', 'valid'=>'required', 'title'=>'转盘活动ID', 'description'=>'转盘活动ID'],
        );
        return $return;
    }

    /**
     * 获取转盘活动详情
     */
    public function get($params)
    {
        $result = kernel::single('syspromotion_lottery')->getInfo($params);
        return $result;
    }
}

