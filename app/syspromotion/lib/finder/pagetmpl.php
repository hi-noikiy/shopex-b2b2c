<?php

/**
 * pagetmpl.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_finder_pagetmpl {
    
    public $column_edit = '操作';
    public $column_edit_order = 1;
    public $column_edit_width = 60;
    
    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
                $url = '?app=syspromotion&ctl=admin_pagetmpl&act=add&finder_id='.$_GET['_finder']['finder_id'].'&ptmpl_id='.$row['ptmpl_id'];
                $target = 'dialog::{title:\''.app::get('syspromotion')->_('编辑模板').'\', width:800, height:580}';
                $title = app::get('syspromotion')->_('编辑');
                $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
    
        }
    }
}