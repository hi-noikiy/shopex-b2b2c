<?php

class syspromotion_finder_voucher {

    public $column_edit = "编辑";
    public $column_edit_order = 1;
    public $column_edit_width = 5;

    public function column_edit(&$colList,$list)
    {
        $nowTime = time();
        foreach($list as $k=>$row)
        {
            $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'edit','ctl'=>'admin_voucher','finder_id'=>$_GET['_finder']['finder_id'],'voucher_id'=>$row['voucher_id']]);

            if( $row['canuse_end_time'] < time() )
            {
                $row['valid_status'] = 0;
            }

            if( $row['apply_begin_time'] <= time() )
            {
                $title = '查看';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('查看购物券信息').'\', width:920, height:600}';
            }
            else
            {
                $title = '编辑';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('编辑购物券信息').'\', width:920, height:600}';
            }

            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';

            if( $row['valid_status'] )
            {
                $validStatusUrl = url::route('shopadmin', ['app'=>'syspromotion','act'=>'edit','ctl'=>'admin_voucher','finder_id'=>$_GET['_finder']['finder_id'],'voucher_id'=>$row['voucher_id'], 'stop'=>true]);
                $colList[$k] .= '  <a href="' . $validStatusUrl . '" target="dialog::{ title:\''.app::get('sysuser')->_('终止购物券').'\', width:920, height:600}">终止</a>';
            }
        }
    }
 }
