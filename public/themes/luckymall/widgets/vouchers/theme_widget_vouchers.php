<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_vouchers( &$setting )
{
    $voucherIds = $setting['vouchers'];

    $data = app::get('topc')->rpcCall('promotion.voucher.list.get', ['voucher_id'=>implode(',',$voucherIds),'fields'=>'voucher_id,limit_cat,used_platform,deduct_money,cansend_start_time,cansend_end_time,valid_status']);

    $catIds = implode(',',array_column($data['list'],'limit_cat'));
    $catData = app::get('topc')->rpcCall('category.cat.get.info',['cat_id'=>$catIds,'level'=>1,'fields'=>'cat_name']);
    foreach( $data['list'] as $row )
    {
        if( $row['valid_status'] == '1' && $row['cansend_start_time'] <= time() &&  time() <= $row['cansend_end_time'] )
        {
            foreach( explode(',',$row['limit_cat']) as $catId )
            {
                $row['cat_name'][] = $catData[$catId]['cat_name'];
            }
            $row['cat_name'] = implode('，',$row['cat_name']);

            $platformArr = [
                'pc' => '电脑端',
                'app' => 'APP端',
                'wap' => 'H5端',
            ];
            foreach( explode(',',$row['used_platform']) as $platform)
            {
                $row['platform'][] = $platformArr[$platform];
            }

            $row['platform'] = implode('，',$row['platform']);
            $row['deduct_money'] = floatval($row['deduct_money']);
            $setting['list'][] = $row;
        }
    }
    return $setting;
}
?>
