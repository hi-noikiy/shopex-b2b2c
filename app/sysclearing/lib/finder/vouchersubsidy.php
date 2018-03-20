<?php

class sysclearing_finder_vouchersubsidy {

    public $column_edit = '操作';
    public $column_edit_order = 1;
    public $column_edit_width = 60;

    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            if($row['status']=='2')
            {
                $colList[$k] = '补贴已发放';
            }
            else
            {
                $url = '?app=sysclearing&ctl=admin_subsidy_voucher&act=confirm&finder_id='.$_GET['_finder']['finder_id'].'&subsidy_no='.$row['subsidy_no'].'&shop_id='.$row['shop_id'].'&voucher_id='.$row['voucher_id'];
                $target = 'dialog::{title:\''.app::get('sysclearing')->_('补贴发放确认').'\', width:300, height:200}';
                $title = app::get('sysclearing')->_('补贴发放确认');
                $colList[$k] = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
            }
        }
    }

}
