<?php
class topc_ctl_trade extends topc_controller{

    var $noCache = true;

    public function __construct(&$app)
    {
        parent::__construct();
        theme::setNoindex();
        theme::setNoarchive();
        theme::setNofolow();
        theme::prependHeaders('<meta name="robots" content="noindex,noarchive,nofollow" />\n');
        $this->title=app::get('topc')->_('订单中心');
        // 检测是否登录
        if( !userAuth::check() )
        {
            redirect::action('topc_ctl_passport@signin')->send();exit;
        }
    }

    public function create()
    {
        $postData                = input::get();
        $postData['mode']        = $postData['mode'] ? $postData['mode'] :'cart';
        $postData['source_from'] = 'pc';

        $postData['user_id']   = userAuth::id();
        $postData['user_name'] = userAuth::getLoginName();

        //配送方式
        foreach( $postData['shipping'] as $shopId=>$shipping )
        {
            $postData['shipping_type'][] = [
                'shop_id' => $shopId,
                'type'    => $shipping['shipping_type'],
                'ziti_id' => ($shipping['shipping_type'] == 'ziti') ? $postData['ziti'][$shopId]['ziti_id'] : null,
            ];
        }
        unset($postData['shipping']);
        $postData['shipping_type'] = json_encode($postData['shipping_type']);

        //订单备注
        $markData = $postData['mark'];
        unset($postData['mark']);
        if( $markData )
        {
            foreach( $markData as $shopId=>$mark )
            {
                if( $mark )
                {
                    $postData['mark'][] = [
                        'shop_id' =>$shopId,
                        'memo' =>$mark,
                    ];
                }
            }
            $postData['mark'] = json_encode($postData['mark']);
        }

        //发票信息处理
        $postData['invoice_type']    = !$postData['invoice']['need_invoice'] ? 'notuse' : $postData['invoice']['invoice_type'];
        if( $postData['invoice_type'] == 'normal' )
        {
            $postData['invoice_content']['title'] = $postData['invoice']['invoice_title'];
            $postData['invoice_content']['content'] = $postData['invoice']['invoice_content'];
        }
        elseif( $postData['invoice_type'] == 'vat' )
        {
            $postData['invoice_content'] = $postData['invoice']['invoice_vat'];
        }
        $postData['invoice_content'] = json_encode($postData['invoice_content']);
        unset($postData['invoice']);

        try
        {
           $createFlag = app::get('topc')->rpcCall('trade.create',$postData);
           if( $createFlag )
           {
               $countData = app::get('topc')->rpcCall('trade.cart.getCount', ['user_id' => userAuth::id()], 'buyer');
               userAuth::syncCookieWithCartNumber($countData['number']);
               userAuth::syncCookieWithCartVariety($countData['variety']);
           }
        }
        catch(Exception $e)
        {
            return $this->splash('error',null,$e->getMessage(),true);
        }

        try
        {
            if($postData['payment_type'] == "online")
            {
                $params['tid'] = $createFlag;
                $params['user_id'] = userAuth::id();
                $params['user_name'] = userAuth::getLoginName();
                $paymentId = kernel::single('topc_payment')->getPaymentId($params);
                $redirect_url = url::action('topc_ctl_paycenter@index',array('payment_id'=>$paymentId,'merge'=>true));
            }
            else
            {
                $redirect_url = url::action('topc_ctl_paycenter@index',array('tid' => implode(',',$createFlag)));
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $url = url::action('topc_ctl_member_trade@tradeList');
            return $this->splash('success',$url,$msg,true);
        }
        return $this->splash('success',$redirect_url,'订单创建成功',true);
    }
}


