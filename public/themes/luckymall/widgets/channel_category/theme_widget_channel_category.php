<?php

function theme_widget_channel_category(&$setting){
    // 判断是否首页
    $returnData = $setting;
    if (route::currentRouteName() == 'topc')
    {
        $returnData['isindex'] = true;
    }
    $cat_list = app::get('topc')->rpcCall('category.cat.get.list',array('fields'=>'cat_id,cat_name'));
    foreach($cat_list as $vlv1)
    {
        if( $vlv1['cat_id'] == $setting['topics_cat_id'] ){
            $returnData['topics_catlist'] = $vlv1;
        }
    }

    return $returnData;
}

?>