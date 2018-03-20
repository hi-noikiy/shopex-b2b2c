<?php
/**
 * topapi
 *
 * -- promotion.scratchcard.detail
 * -- 获取刮刮卡详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_scratchcard_detail implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取刮刮卡详情';

    public function setParams()
    {
        return array(
            'scratchcard_id' => ['type'=>'int', 'valid'=>'required', 'desc'=>'刮刮卡id'],
        );
        return $return;
    }

    public function handle($params)
    {
        $apiParams = [
            'user_id' => $params['user_id'],
            'scratchcard_id' => $params['scratchcard_id'],
        ];
        $scratchcard = app::get('topwap')->rpcCall('promotion.scratchcard.get', $apiParams);
        if($scratchcard['scratchcard']['background_url']){
            $scratchcard['scratchcard']['background_url'] = base_storager::modifier($scratchcard['scratchcard']['background_url']);
        }


        return $scratchcard;
    }
}

