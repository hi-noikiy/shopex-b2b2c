<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/*基础配置项*/
$setting['author']='tangyongchuan@shopex.cn';
$setting['version']='v1.0';
$setting['name']='app下载提示';
$setting['order']=0;
$setting['stime']='2016-12';
$setting['catalog']='辅助信息';
$setting['description']='判断手机是否安装app,并提供下载';
$setting['userinfo']='';
$setting['usual'] = '1';
$setting['tag']    = 'auto';
$setting['template'] = array(
    'default.html'=>app::get('topm')->_('默认')
);
?>
