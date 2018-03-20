<?php
/**
 * topapi
 *
 * -- promotion.trailingmarketing.setting
 * -- 订单结算取消使用优惠券
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_trailingmarketing_setting implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '尾随营销配置信息';

    public function setParams()
    {
        return array(

        );
        return $return;
    }

    public function handle($params)
    {
        $setting = unserialize(app::get('syspromotion')->getConf('trailingmarketing'));

        $pagedata = [];
        if($setting['status'] && $setting['platform'] !='1')
        {
            if($setting['type'] =='scratchcard' && $setting['scratchcard_id']){
                $scratchcard = app::get('topapi')->rpcCall('promotion.scratchcard.get',['scratchcard_id'=>$setting['scratchcard_id']]);
                if($scratchcard['scratchcard']['status'] == 'active'){
                    $pagedata['marketingSetting'] = $setting;
                    $pagedata['scratchcard'] = $scratchcard['scratchcard'];
                }
            }
        }
        return $pagedata;
    }
}

