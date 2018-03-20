<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 删除单条组合促销促销信息
 * promotion.package.delete
 */
final class syspromotion_api_package_packageDelete {

    public $apiDescription = '删除单条组合促销促销信息';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'    => ['type' => 'int', 'valid' => 'required', 'example' => '', 'desc' => '店铺ID必填'],
            'package_id' => ['type' => 'int', 'valid' => 'required', 'example' => '', 'desc' => '组合促销促销ID必填'],
        );

        return $return;
    }

    /**
     * 根据组合促销促销ID删除组合促销促销
     * @param  array $packageId 组合促销促销id
     * @return bool
     */
    public function packageDelete($params)
    {
        return kernel::single('syspromotion_data_object')
            ->setPromotion('package', $params['shop_id'])
            ->deletePromotion($params['package_id']);
    }

}

