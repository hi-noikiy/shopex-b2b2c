<?php
/**
 * ShopEx licence
 *
 * - promotion.lottery.issue
 * - 转盘抽奖发放奖励
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 *
 */
final class syspromotion_api_lottery_bonusIssue {

    public $apiDescription = '获取转盘抽奖奖励';

    public function getParams()
    {
        $return['params'] = array(
            'lottery_id' => ['type'=>'int','valid'=>'required', 'title'=>'转盘活动ID','description'=>'转盘活动ID'],
            'user_id' => ['type'=>'int','valid'=>'required', 'title'=>'转盘活动ID', 'description'=>'用户id'],

        );
        return $return;
    }

    /**
     * 获取转盘抽奖奖励
     */
    public function issue($params)
    {
        if(!$params['user_id']){
            throw new \LogicException('会员参数错误');
        }
        if(!$params['lottery_id']){
            throw new \LogicException("活动错误");
        }
        $result = kernel::single('syspromotion_data_lottery',$params['bonus_type'])->issue($params);
        return $result;
    }
}

