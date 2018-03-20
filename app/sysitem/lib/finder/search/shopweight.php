<?php
class sysitem_finder_search_shopweight{

    public $column_edit = '编辑';
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list){
        foreach($list as $k=>$row)
        {
            $url = '?app=sysitem&ctl=admin_search_setting&act=editShopWeight&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['shop_id'];
            $target = 'dialog::  {title:\''.app::get('sysitem')->_('编辑权重').'\', width:500, height:400}';

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . app::get('sysitem')->_('编辑') . '</a>';
        }
    }

}
