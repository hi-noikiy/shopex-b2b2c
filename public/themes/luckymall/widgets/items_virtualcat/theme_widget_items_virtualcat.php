<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_items_virtualcat(&$setting)
{

    $returnData['data'] = app::get('topc')->rpcCall('category.virtualcat.get.list',array('fields'=>'virtual_cat_id,virtual_cat_name,url','platform'=>'pc'));
   // echo "<pre>";print_r($returnData);exit;
    return $returnData;
}
