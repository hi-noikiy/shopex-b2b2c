<?php
/**
 * topapi
 *
 * -- promotion.scratchcard.prize.gen
 * -- 生成刮刮卡奖品
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_scratchcard_prizeGen implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '生成刮刮卡奖品';

    public function setParams()
    {
        return array(
            'scratchcard_id' => ['type'=>'int', 'valid'=>'required', 'desc'=>'刮刮卡id'],
            'payment_id' => ['type'=>'string', 'valid'=>'', 'desc'=>'支付单号'],
        );
        return $return;
    }

    public function handle($params)
    {
        try
        {
            $apiParams = [
                'user_id' => $params['user_id'],
                'scratchcard_id' => $params['scratchcard_id'],
                'rel_paymentid' => $params['payment_id'],
            ];
            $data = app::get('topwap')->rpcCall('promotion.scratchcard.receive',$apiParams);
            $result = [
                'success' => true,
                'prizeInfo' => $data['scratchcard_result'],
                'leftTimes' => $data['leftTimes'],
                'scratchcard' => $data['scratchcard'],
            ];
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $result = [
                'error' => true,
                'msg' => $msg,
            ];
        }

        return $result;
    }
}

