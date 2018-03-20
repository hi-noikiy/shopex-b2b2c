<?php

class syspromotion_finder_voucher_register {

    public $column_edit = "操作";
    public $column_edit_order = 1;
    public $column_edit_width = 5;

    public function column_edit(&$colList,$list)
    {
        $voucherData = app::get('syspromotion')->model('voucher');
        $voucherIds = array_column($list, 'voucher_id');
        foreach($list as $k=>$row)
        {
            $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'edit','ctl'=>'admin_voucher_register','finder_id'=>$_GET['_finder']['finder_id'],'voucher_id'=>$row['voucher_id'],'shop_id'=>$row['shop_id'], 'finderview'=>'detail_basic','action'=>'detail','singlepage'=>'true']);

            if($row['verify_status']=='pending' && $row['valid_status'] )
            {
                $title = '审核';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('审核商家参加购物券报名').'\', width:920, height:600}';
            }
            elseif($row['verify_status']=='agree' && $row['valid_status'] )
            {
                $title = '终止';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('终止商家参加购物券').'\', width:920, height:600}';
            }
            else
            {
                $title = '查看';
                $target = 'dialog::{ title:\''.app::get('sysuser')->_('查看购物券报名信息').'\', width:920, height:600}';
            }
            $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
        }
    }
}


