<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 添加满折促销数据
 */
final class syspromotion_api_fulldiscount_fulldiscountAdd {

    public $apiDescription = '添加满折促销数据';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'            => ['type'=>'int',     'valid'=>'required',          'example'=>3,   'desc'=>'店铺ID'],
            'fulldiscount_name'  => ['type'=>'string',  'valid'=>'required',         'example'=>'',   'desc'=>'满折促销名称', 'msg'=>'请填写促销名称'],
            'condition_value'    => ['type'=>'jsonArray',  'valid'=>'required',         'example'=>'', 'desc'=>'满折规则', 'msg'=>'请添加满折规则', 'params'=>array(
                'full' => ['type'=>'int', 'valid'=>'numeric', 'example'=>'100', 'desc'=>'消费满多少金额', 'msg'=>'促销满折金额必须为数字'],
                'discount'=> ['type'=>'int', 'valid'=>'numeric', 'example'=>'20', 'desc'=>'折扣百分比', 'msg'=>'促销满折折扣必须为数字'],
            )],
            'join_limit'         => ['type'=>'int',     'valid'=>'required|integer|min:1','example'=>10,            'desc'=>'用户可参与次数', 'msg'=>'请填写可参与次数|请填写正整数|请填写正整数'],
            'valid_grade'        => ['type'=>'string',  'valid'=>'required',            'example'=>'1,2,3,4,5',     'desc'=>'适用会员,会员等级ID', 'msg'=>'请选择使用的会员等级'],
            'used_platform'      => ['type'=>'string',  'valid'=>'required|in:0,1,2,3', 'example'=>'1',             'desc'=>'使用平台 0 全场可用，1只能用于PC，2只能用于WAP，3只能用于APP', 'msg'=>'请选择使用平台'],
            'fulldiscount_desc'     => ['type'=>'string',  'valid'=>'max:50',           'example'=>'',              'desc'=>'规则描述', 'msg'=>'规则描述不能超过50个字'],
            'start_time'         => ['type'=>'string',  'valid'=>'required',            'example'=>'1483150679',    'desc'=>'促销有效开始时间'],
            'end_time'           => ['type'=>'string',  'valid'=>'required',            'example'=>'1483150680',    'desc'=>'促销有效结束时间'],
            'fulldiscount_rel_item' => ['type'=>'jsonArray', 'valid'=>'required',       'example'=>'',              'desc'=>'促销关联的商品', 'params'=>array(
                'item_id' => ['type'=>'int',    'valid'=>'required|integer|min:1', 'example'=>'', 'desc'=>'商品ID'],
                'sku_id'  => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'SKU ID以逗号隔开, 数据为空则表示所有规格都参加促销'],
            ), 'msg'=>'请选择促销商品'],
        );

        return $return;
    }

    /**
     *  添加满折促销数据
     * @param  array $apiData 满折促销各种值
     * @return
     */
    public function fulldiscountAdd($apiData)
    {
        $apiData['fulldiscount_name'] = strip_tags($apiData['fulldiscount_name']);
        $apiData['fulldiscount_desc'] = strip_tags($apiData['fulldiscount_desc']);

        $forPlatform = intval($apiData['used_platform']);
        $apiData['used_platform'] = $forPlatform ? $forPlatform : '0';;

        return kernel::single('syspromotion_data_object')
            ->setPromotion('fulldiscount', $apiData['shop_id'])
            ->savePromotion($apiData);
    }

}

