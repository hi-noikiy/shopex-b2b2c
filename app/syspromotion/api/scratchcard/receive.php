<?php
/**
 * ShopEx licence
 *
 * - promotion.scratchcard.receive
 * - 会员领取刮刮卡
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 *
 */
final class syspromotion_api_scratchcard_receive {

    public $apiDescription = '会员领取刮刮卡';

    public function getParams()
    {
        $return['params'] = array(
            'scratchcard_id' => ['type'=>'int', 'valid'=>'required', 'title'=>'刮刮卡活动ID', 'description'=>'刮刮卡活动ID'],
            'user_id' => ['type'=>'int', 'valid'=>'required', 'title'=>'会员ID', 'description'=>'会员ID,可以获取该会员剩余多少次机会'],
            'rel_paymentid' => ['type'=>'int', 'valid'=>'', 'title'=>'关联支付单号', 'description'=>'订单号，订单尾随营销关联支付单号'],
        );
        return $return;
    }

    /**
     * 获取转盘活动详情
     */
    public function receive($params)
    {
        $scratchcardId = $params['scratchcard_id'];
        $userId = $params['user_id'];
        $rel_paymentid = $params['rel_paymentid'];

        try{
            $db = app::get('syspromotion')->database();
            $transaction_status = $db->beginTransaction();

            $result = kernel::single('syspromotion_scratchcard_object')->receiveScratchcard($scratchcardId, $userId,$rel_paymentid);
            $db->commit($transaction_status);
        }catch(Exception $e){
            $db->rollback();
            throw $e;
        }


        return $result;
    }
}

