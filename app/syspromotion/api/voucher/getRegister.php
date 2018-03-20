<?php
/**
 * promotion.voucher.register.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取指定购物券详情
 */
final class syspromotion_api_voucher_getRegister {

    public $apiDescription = '获取购物券商家报名信息';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id' => ['type'=>'string', 'valid'=>'required|integer', 'desc'=>'购物券Id'],
            'shop_id'    => ['type'=>'string', 'valid'=>'required|integer', 'desc'=>'店铺Id'],
            'fields'     => ['type'=>'field_list', 'valid'=>'', 'example'=>'', 'description'=>'需要的字段']
        );

        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $row = empty($params['fields']) ? '*' : $params['fields'];

        $filter['shop_id'] = $params['shop_id'];
        $filter['voucher_id'] = $params['voucher_id'];
        $data = app::get('syspromotion')->model('voucher_register')->getRow($row, $filter);

        return $data;
    }
}

