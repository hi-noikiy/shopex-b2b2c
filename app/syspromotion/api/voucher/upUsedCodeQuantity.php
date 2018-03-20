<?php
/**
 * promotion.voucher.code.usedQuantity
 *
 * ShopEx licence *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 更新购物券用户使用数量
 */
final class syspromotion_api_voucher_upUsedCodeQuantity {

    public $apiDescription = '更新购物券用户使用数量';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id' => ['type'=>'int',    'valid'=>'required', 'desc'=>'购物券ID'],
            'quantity'   => ['type'=>'string', 'valid'=>'required', 'example'=>'1',  'desc'=>'此次使用的购物券数量'],
        );

        return $return;
    }

    /**
     * @return
     */
    public function handle($apiData)
    {
        return kernel::single('syspromotion_data_promotion_voucher')->updateUsedQuantity($apiData['voucher_id'], $apiData['quantity']);
    }
}

