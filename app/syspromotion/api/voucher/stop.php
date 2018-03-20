<?php
/**
 * promotion.voucher.stop
 *
 * ShopEx licence *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 平台终止指定购物券活动
 */
final class syspromotion_api_voucher_stop {

    public $apiDescription = '平台终止指定购物券活动';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id'    => ['type'=>'int',    'valid'=>'required', 'desc'=>'购物券ID'],
        );

        return $return;
    }

    /**
     * @return
     */
    public function update($apiData)
    {
        //规则表进行终止
        app::get('syspromotion')->model('voucher')->update(['valid_status'=>0], $apiData);

        //商家报名表终止
        app::get('syspromotion')->model('voucher_register')->update(['valid_status'=>0], $apiData);

        app::get('syspromotion')->rpcCall('user.voucher.stop',['voucher_id'=>$apiData['voucher_id']]);

        return true;
    }
}

