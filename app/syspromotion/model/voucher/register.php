<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syspromotion_mdl_voucher_register extends dbeav_model{

    public function modifier_shop_id(&$colList)
    {
        foreach( $colList as $id )
        {
            $shopids[] = $id;
        }
        // shop.get.list
        $shopdata = app::get('sysshop')->model('shop')->getList('shop_name,shop_id',array('shop_id'=>$shopids));
        $shopdata = array_bind_key($shopdata, 'shop_id');
        foreach($colList as $k=>$row)
        {
            if($shopdata[$row]['shop_name'])
            {
            $colList[$k] = $shopdata[$row]['shop_name'];
            }
        }
    }

    public function modifier_voucher_id(&$colList)
    {
        foreach( $colList as $id )
        {
            $voucherIds[] = $id;
        }
        // shop.get.list
        $data = app::get('syspromotion')->model('voucher')->getList('voucher_name,voucher_id',array('voucher_id'=>$voucherIds));
        $data = array_bind_key($data, 'voucher_id');
        foreach($colList as $k=>$row)
        {
            if($data[$row]['voucher_name'])
            {
                $colList[$k] = $data[$row]['voucher_name'];
            }
        }
    }
}
