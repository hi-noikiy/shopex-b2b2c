<?php
class syspromotion_mdl_promotions extends dbeav_model{

    public function modifier_shop_id(&$colList)
    {
        $shopids = implode(',', $colList);
        $shopinfo = app::get('syspromotion')->rpcCall('shop.get.list',array('shop_id'=>$shopids,'fields'=>'shop_id'));
        $shopdata = array_bind_key($shopinfo,'shop_id');

        foreach($colList as $k=>$row)
        {
            if($shopdata[$row]['shop_name'])
            {
                $colList[$k] = $shopdata[$row]['shop_name'];
            }
        }
    }
}