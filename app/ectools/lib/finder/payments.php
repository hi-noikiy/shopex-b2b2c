<?php
class ectools_finder_payments{

    public $detail_basic = '详情';
    public function detail_basic($id)
    {
        $objMdlPayments = app::get('ectools')->model('payments');
        $pagedata = $objMdlPayments->getRow('status,pay_type,pay_app_id,pay_name,account,bank,pay_account,currency,paycost,ip,memo,trade_no,thirdparty_account,user_id,user_name,op_id,op_name',array('payment_id'=>$id));

        return view::make('ectools/payments.html', $pagedata)->render();
    }
}
