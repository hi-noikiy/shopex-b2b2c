<?php
/**
 * ShopEx licence
 *
 * - promotion.scratchcard.exchange
 * - 获取抽奖结果
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 *
 */
final class syspromotion_api_scratchcard_exchange {

    public $apiDescription = '获取刮刮卡的奖品';

    public function getParams()
    {
        $return['params'] = array(
            'scratchcard_result_id' => ['type'=>'int', 'valid'=>'required', 'title'=>'刮刮卡活动ID', 'description'=>'刮刮卡活动ID'],
        );
        return $return;
    }

    /**
     * 获取转盘活动详情
     */
    public function exchange($params)
    {
        try{
            $db = app::get('syspromotion')->database();
            $transaction_status = $db->beginTransaction();

            $scratchcardResultId = $params['scratchcard_result_id'];
            $result = kernel::single('syspromotion_scratchcard_object')->exchangeScratchcard($scratchcardResultId);
            $db->commit($transaction_status);
        }catch(Exception $e){
            $db->rollback();
            throw $e;
        }


        return $result;
    }
}

