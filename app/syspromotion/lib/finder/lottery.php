<?php

class syspromotion_finder_lottery {

    public $column_edit = "编辑";
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_edit(&$colList,$list)
    {
        foreach($list as $k=>$row)
        {
            $editUrl = url::route('shopadmin', ['app'=>'syspromotion','act'=>'edit','ctl'=>'admin_lottery','_finder[finder_id]'=>$_GET['_finder']['finder_id'],'lottery_id'=>$row['lottery_id']]);
            $editTitle = '编辑';
            $editTarget = 'dialog::{ title:\''.app::get('sysuser')->_('编辑转盘信息').'\', width:900, height:600}';
            $logTitle = '查看获奖明细';
            $logTarget = '_blank';
            $logUrl = '?app=desktop&amp;act=alertpages&amp;goto=%3Fapp%3Dsyspromotion%26ctl%3Dadmin_lottery%26act%3DlogDetail%26lottery_id%3D'.$row['lottery_id'].'%26nobuttion%3D1';

            $colList[$k] = '<a href="' . $editUrl . '" target="' . $editTarget . '">' . $editTitle . '</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $logUrl . '" target="' . $logTarget . '">' . $logTitle . '</a>';
        }
    }

    public $column_preview = "预览";
    public $column_preview_order = 2;
    public $column_preview_width = 10;
    public function column_preview(&$colList,$list)
    {
        foreach ($list as $key => $value) {
            $url = url::action('topc_ctl_lottery@index', ['lottery_id'=>$value['lottery_id']]);
            $colList[$key] = '<a href="' . $url . '" target="_blank" title="只有开启活动后，才可进行预览"> pc预览 </a>';
        }
    }
 }
