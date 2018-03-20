<?php
/**
 * promotion.voucher.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取指定购物券详情
 */
final class syspromotion_api_voucher_get {

    public $apiDescription = '获取购物券详情购物券';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id'   => ['type'=>'string', 'valid'=>'required_without:voucher_name', 'desc'=>'购物券Id'],
            'voucher_name' => ['type'=>'string', 'valid'=>'required_without:voucher_id', 'desc'=>'购物券名称'],
            'fields'     => ['type'=>'field_list', 'valid'=>'', 'example'=>'', 'description'=>'需要的字段']
        );

        return $return;
    }

    /**
     *  获取购物券详情购物券
     * @return
     */
    public function get($params)
    {
        $row = empty($params['fields']) ? '*' : $params['fields'];
        unset($params['fields']);
        $data = app::get('syspromotion')->model('voucher')->getRow($row, $params);

        if( $data )
        {
            $filter = [
                'verify_status' => 'agree',
                'valid_status' => '1',
                'voucher_id' => $params['voucher_id'],
            ];

            //获取已经报名成功的店铺ID
            $registerShop = app::get('syspromotion')->model('voucher_register')->getList('shop_id,voucher_id,cat_id', $filter);
            $data['registerShop'] = $registerShop;
        }

        return $data;
    }
}

