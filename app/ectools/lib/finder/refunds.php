<?php
class ectools_finder_refunds{

    public $detail_basic = '详情';
    public function detail_basic($id)
    {
        $objMdlRefunds = app::get('ectools')->model('refunds');

        $pagedata = $objMdlRefunds->getRow('cur_money,refund_bank,refund_account,refund_people,receive_bank,receive_account,beneficiary,rufund_type,status,op_id,refunds_type,aftersales_bn,pay_app_id,created_time,finish_time,memo,oid,tid',array('refund_id'=>$id));
        return view::make('ectools/refunds.html', $pagedata)->render();
    }
}
