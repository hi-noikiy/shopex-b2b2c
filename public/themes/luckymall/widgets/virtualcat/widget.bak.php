<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='hlj@shopex.cn';
$setting['version']='v1.0';
$setting['name']='商品虚拟分类';
$setting['stime']='2016-10';
$setting['catalog']='辅助信息';
$setting['usual'] = '1';
$setting['description']='商品虚拟分类';
$setting['userinfo']='';
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('topc')->_('默认')
                        );
$categorylist = kernel::single('syscategory_data_virtualcat')->maplist();

foreach( $categorylist as $row )
{
    if( $row['level'] == 1 )
    {
        $selectNode[] = $row;
    }
}

$setting['selectNode'] = $selectNode;
// echo"<pre>";print_r($selectNode);exit();
$setting['selectmaps'] = $selectmaps;

?>
