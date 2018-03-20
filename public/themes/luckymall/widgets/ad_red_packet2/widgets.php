<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/*基础配置项*/
$setting['author']='tylerchao.sh@gmail.com';
$setting['version']='v1.0';
$setting['name']='平台红包（最多10个）';
$setting['order']=0;
$setting['stime']='2016-08';
$setting['catalog']='广告相关';
$setting['description'] = '展示平台可领取的红包，可放最多五种或十种红包供领取。';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

$setting['limit']    = '3';

function getHongbao2()
{
    $params = [
        'hongbao_status'=>'active',
        'platform'=>'pc',
        'page_no'=>1,
        'page_size'=>100,
        'fields'=>'hongbao_id,name,hongbao_list,get_start_time,get_end_time'
    ];

    $hongbaoList = app::get('topc')->rpcCall('promotion.hongbao.list.get', $params);
    if( $hongbaoList['list'] )
    {
        foreach( $hongbaoList['list'] as $key=>$row )
        {
            if( time() >=  $row['get_start_time'] && time() < $row['get_end_time'] )
            {
                rsort($row['hongbao_list']);
                $data[$row['hongbao_id']] = $row;
            }
        }
    }
    return $data;
}

$setting['hongbaolist'] = getHongbao2();
?>
