<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysshop_mdl_shop_notice extends dbeav_model{

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
            else
            {
                $colList[$k] = "所有";
            }
        }
    }

}
