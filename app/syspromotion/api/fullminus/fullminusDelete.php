<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 删除单条满减促销信息
 */
final class syspromotion_api_fullminus_fullminusDelete {

    public $apiDescription = '删除单条满减促销信息';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'      => ['type' => 'int', 'valid' => 'required', 'example' => '', 'desc' => '店铺ID必填'],
            'fullminus_id' => ['type' => 'int', 'valid' => 'required', 'example' => '', 'desc' => '满减促销ID必填'],
        );

        return $return;
    }

    /**
     * 根据满减促销ID删除满减促销
     * @param  array $fullminusId 满减促销id
     * @return bool
     */
    public function fullminusDelete($params)
    {
        return kernel::single('syspromotion_data_object')->setPromotion('fullminus', $params['shop_id'])->deletePromotion($params['fullminus_id']);
    }
}

