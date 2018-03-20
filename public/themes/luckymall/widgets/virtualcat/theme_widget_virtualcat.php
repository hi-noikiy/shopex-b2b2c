<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_virtualcat(&$setting){
	
	if($setting['lv3catId']){
		$lv3catId = $setting['lv3catId'];
		$catInfo = app::get('syscategory')->rpcCall('category.virtualcat.info',array('virtual_cat_id'=>$lv3catId));
		$setting['virtual_cat_name'] = $catInfo['virtual_cat_name'];
	}
	
    return $setting;
}
?>
