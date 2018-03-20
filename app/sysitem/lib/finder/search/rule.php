<?php
class sysitem_finder_search_rule{

    public $column_edit = '编辑';
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list){
        foreach($list as $k=>$row)
        {
            $url = '?app=sysitem&ctl=admin_search_setting&act=editRule&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['rule_id'];
            $target = 'dialog::  {title:\''.app::get('sysitem')->_('编辑权重规则').'\', width:500, height:520}';

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . app::get('sysitem')->_('编辑') . '</a>';
        }
    }

    public $column_used = '使用';
    public function column_used(&$colList, $list)
    {
        $default = app::get('sysitem')->getConf('search_weight_rule');
        foreach($list as $k=>$row)
        {
            if($default && $default == $row['rule_id'] )
            {
                $colList[$k] = '<span style="color:gray;">'.app::get('search')->_('默认').'</span>';
            }
            else
            {
                $colList[$k] = '<a href="javascript:;" onClick="javascript:W.page(\'?app=sysitem&ctl=admin_search_setting&act=setRuleDefault&p[0]='.$row["rule_id"].'\')" >'.app::get('search')->_('设为默认').'</a>';
            }
        }
    }//End Function

}
