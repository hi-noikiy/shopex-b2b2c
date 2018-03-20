<?php
/**
 * ShopEx licence
 * - promotion.lottery.updateAddr
 * - 更新转盘抽奖收货地址
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class syspromotion_api_lottery_updateAddr {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新转盘抽奖收货地址';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'result_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'获奖id', 'desc'=>'获奖id'],
            'lottery_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'转盘id', 'desc'=>'转盘id'],
            'user_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'用户id', 'desc'=>'用户id'],
        );
        return $return;
    }

    /**
     * 更新转盘抽奖收货地址
     *
     * @desc 更新转盘抽奖收货地址
     * @return bool  
     */
    public function update($params)
    {
        $filter = array(
            'user_id'=>$params['user_id'],
            'lottery_id'=>$params['lottery_id'],
            'result_id'=>$params['result_id'],
        );
        $updateData = $params['addrData'];

        $objMdllottery = app::get('syspromotion')->model('lottery_result');
        $data = $objMdllottery->getRow('*', $filter);

        if( !$data )
        {
            throw new \LogicException('更新的转盘抽奖活动数据不存在');
        }
        
        return $objMdllottery->update($updateData, $filter);
    }
}

