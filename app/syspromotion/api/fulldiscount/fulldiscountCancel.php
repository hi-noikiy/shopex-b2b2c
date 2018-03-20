<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 取消单条满折促销
 */
final class syspromotion_api_fulldiscount_fulldiscountCancel {

    public $apiDescription = '取消单条满折促销';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'         => ['type' => 'int', 'valid' => 'required', 'example' => '', 'desc' => '店铺ID必填'],
            'fulldiscount_id' => ['type' => 'int', 'valid' => 'required', 'example' => '', 'desc' => '满折促销ID必填'],
        );

        return $return;
    }

    /**
     * @brief 根据满折促销ID取消满折促销
     * @author lujy
     * @param $params array
     *
     * @return
     */
    public function fulldiscountCancel($params)
    {
        return kernel::single('syspromotion_data_object')->setPromotion('fulldiscount', $params['shop_id'])->cancelPromotion($params['fulldiscount_id']);
    }

}

