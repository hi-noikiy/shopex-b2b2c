<?php
/**
 * ShopEx licence
 * - promotion.lottery.updateStatus
 * - 更新转盘抽奖活动状态
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class syspromotion_api_lottery_updateStatus {

    /**
     * 接口作用说明
     */
    public $apiDescription = '更新转盘抽奖活动状态';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'lottery_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'红包ID', 'desc'=>'转盘抽奖活动ID'],
            'status'     => ['type'=>'string', 'valid'=>'required|in:active,stop',  'title'=>'转盘抽奖活动状态', 'desc'=>'转盘抽奖活动状态'],
        );
        return $return;
    }

    /**
     * 更新转盘抽奖活动状态
     *
     * @desc 更新转盘抽奖活动状态接口
     * @return bool  
     */
    public function update($params)
    {
        $objMdllottery = app::get('syspromotion')->model('lottery');        
        $data = $objMdllottery->getRow('*', ['lottery_id'=>$params['lottery_id']]);

        if( !$data )
        {
            throw new \LogicException('更新的转盘抽奖活动不存在');
        }

        return $objMdllottery->update(['status'=>$params['status']], ['lottery_id'=>$data['lottery_id']]);

         
    }
}

