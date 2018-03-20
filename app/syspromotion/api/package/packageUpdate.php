<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 更新组合促销促销数据
 * promotion.package.update
 */
final class syspromotion_api_package_packageUpdate {

    public $apiDescription = '更新组合促销促销数据';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'       => ['type'=>'int',    'valid'=>'required', 'example' => '', 'desc' => '店铺ID'],
            'package_id'    => ['type'=>'int',    'valid'=>'required', 'example'=>'',   'desc'=>'组合促销促销id'],
            'package_name'  => ['type'=>'string', 'valid'=>'required|max:10', 'example' => '',    'desc'=>'组合促销促销名称', 'msg'=>'请填写组合促销名称|促销名称不能超过10个字'],
            'valid_grade'   => ['type'=>'string', 'valid'=>'required', 'example'=>'1,2,3,4,5',    'desc'=>'适用会员,会员等级ID', 'msg'=>'请选择使用的会员等级'],
            'used_platform' => ['type'=>'string', 'valid'=>'required|in:0,1,2,3', 'example'=>'1', 'desc'=>'使用平台 0 全场可用，1只能用于PC，2只能用于WAP，3只能用于APP', 'msg'=>'请选择使用平台'],
            'start_time'    => ['type'=>'string', 'valid'=>'required',  'example'=>'1483150679',  'desc'=>'促销有效开始时间'],
            'end_time'      => ['type'=>'string', 'valid'=>'required',  'example'=>'1483150680',  'desc'=>'促销有效结束时间'],
            'package_rel_item' => ['type'=>'jsonArray', 'valid'=>'required','example'=>'',        'desc'=>'促销关联的商品', 'params'=>array(
                'item_id'       => ['type'=>'int',    'valid'=>'required|integer|min:1', 'example'=>'', 'desc'=>'商品ID'],
                'sku_id'        => ['type'=>'string', 'valid'=>'',                       'example'=>'', 'desc'=>'SKU ID以逗号隔开, 数据为空则表示所有规格都参加促销'],
                'package_price' => ['type'=>'string', 'valid'=>'required', 'example'=>'', 'desc'=>'商品促销价格', 'msg'=>'请填写组合促销商品的促销价格'],
            ), 'msg'=>'请选择促销商品'],
        );


        return $return;
    }

    /**
     *  编辑组合促销促销
     * @param  array $apiData api数据
     * @return bool
     */
    public function packageUpdate($apiData)
    {
        $apiData['package_name'] = strip_tags($apiData['package_name']);

        $forPlatform = intval($apiData['used_platform']);
        $apiData['used_platform'] = $forPlatform ? $forPlatform : '0';;

        return kernel::single('syspromotion_data_object')
            ->setPromotion('package', $apiData['shop_id'])
            ->savePromotion($apiData);
    }
}

