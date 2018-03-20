<?php
/**
 * topapi
 *
 * -- promotion.scratchcard.prize.issue
 * -- 刮刮卡奖项发放
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_scratchcard_prizeIssue implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '刮刮卡奖项发放';

    public function setParams()
    {
        return array(
            'scratchcard_result_id' => ['type'=>'int', 'valid'=>'required', 'title'=>'刮刮卡活动ID', 'description'=>'刮刮卡活动ID'],
        );
        return $return;
    }

    public function handle($params)
    {
        try
        {
            $result = app::get('topapi')->rpcCall('promotion.scratchcard.exchange',['scratchcard_result_id'=>$params['scratchcard_result_id']]);
        }
        catch(Exception $e)
        {
            throw new $e;
        }

        return $result;
    }
}

