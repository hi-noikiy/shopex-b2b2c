<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_floor_channel( &$setting )
{
    if( !$setting['slider_type'] )
    {
        if( $setting['brand'] )
        {
            $data = app::get('topc')->rpcCall('category.brand.get.list',['brand_id'=>implode(',',$setting['brand']),['fields'=>'brand_id,brand_logo']]);
            $i = 0;
            $k = 1;
            foreach( $data as $n=>$row )
            {
                $picData[$n]['link'] = $row['brand_logo'];//图片地址
                $picData[$n]['linkinfo'] = $row['brand_name'];//图片描述
                $picData[$n]['linktarget'] = url::action('topc_ctl_list@index',['search_keywords'=>$row['brand_name']]);//链接地址


            }
            $setting['picData'] = $picData;
        }
    }
    else
    {
        foreach( (array)$setting['pic'] as $n=>$row )
        {
            $picData[][$n] = $row;
        }
        $setting['picData'] = $picData;
    }

    return $setting;
}
?>
