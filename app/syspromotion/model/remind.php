<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syspromotion_mdl_remind extends dbeav_model{

    public function modifier_user_id(&$colList)
    {
        if( $colList )
        {
            $userIds = implode(',',$colList);
            $userdata = app::get('systrade')->rpcCall('user.get.account.name',['user_id'=>$userIds]);
            foreach($colList as $k=>$row)
            {
                $colList[$k] = $userdata[$row];
            }
        }
    }
}