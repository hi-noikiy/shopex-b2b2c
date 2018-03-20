<?php

class sysaftersales_ctl_refunds extends desktop_controller {

    public $workground = 'sysaftersales.workground.aftersale';

    public function index()
    {
        return $this->finder(
            'sysaftersales_mdl_refunds',
            array(
                'title'=>app::get('sysaftersales')->_('申请退款列表'),
                 'use_buildin_delete'=>false,
            )
        );
    }

    public function _views()
    {
        $subMenu = array(
            0=>array(
                'label'=>app::get('systrade')->_('全部'),
                'optional'=>false,
            ),
            1=>array(
                'label'=>app::get('systrade')->_('退款处理'),
                'optional'=>false,
                'filter'=>array(
                    'status'=>array('3','4','5'),
                ),
            ),
            2=>array(
                'label'=>app::get('systrade')->_('待商家审核'),
                'optional'=>false,
                'filter'=>array(
                    'status'=>'0',
                ),
            ),
            3=>array(
                'label'=>app::get('systrade')->_('已完成'),
                'optional'=>false,
                'filter'=>array(
                    'status'=>'1',
                ),
            ),
            4=>array(
                'label'=>app::get('systrade')->_('已关闭'),
                'optional'=>false,
                'filter'=>array(
                    'status'=>['2','4'],
                ),
            ),
        );
        return $subMenu;
    }

    public function rejectView($refundsId)
    {
        if( !$refundsId )
        {
            $refundsId = input::get();
        }
        $data = app::get('sysaftersales')->model('refunds')->getRow('aftersales_bn,refunds_id,oid', array('refunds_id'=>$refundsId));
        $pagedata['aftersalesBn'] = $data['aftersales_bn'];
        $pagedata['refundsId'] = $data['refunds_id'];
        return $this->page('sysaftersales/reject.html', $pagedata);
    }

    public function doTeject()
    {
        $this->begin("?app=sysaftersales&ctl=refunds&act=index");

        $postdata = input::get('data');
        if( empty($postdata['explanation']) )
        {
            $this->end(false,'取消原因必填');
        }
        //$params['confirm_from'] = 'admin';
        try
        {
            app::get('sysaftersales')->rpcCall('aftersales.refunds.reject',$postdata);
            $this->adminlog("平台拒绝商家退款[aftersales_bn:{$postdata['aftersales_bn']}]", 1);
        }
        catch(\LogicException $e)
        {
            $this->adminlog("平台拒绝商家退款[aftersales_bn:{$postdata['aftersales_bn']}]", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }
        $this->end('true');
    }

    public function refundsPay($refunds_id)
    {
        $this->begin("?app=sysaftersales&ctl=refunds&act=index");
        $data = app::get('sysaftersales')->model('refunds')->getRow('*', array('refunds_id'=>$refunds_id));
        $pagedata['user']['id'] = kernel::single('desktop_user')->get_id();
        $pagedata['user']['name'] = kernel::single('desktop_user')->get_login_name();
        $user = app::get('sysaftersales')->rpcCall('user.get.account.name',array('user_id'=>$data['user_id']),'buyer');
        $data['user_name'] = $user[$data['user_id']];
        $pagedata['data'] = $data;
        $pagedata['refundFee'] = ecmath::number_minus(array($data['refund_fee'], $data['hongbao_fee']));
        // 获取退款申请单对应原订单支付信息
        $params = ['tids'=>$data['tid'],'fields'=>'*','status'=>'succ'];
        $pagedata['payment'] = app::get('sysaftersales')->rpcCall('trade.payment.list', $params)[$data['tid']];

        return $this->page('sysaftersales/refunds.html', $pagedata);
    }

    public function dorefund()
    {
        $postdata = input::get('data');
        $refundsData = input::get('refundsData');

        // $this->begin("?app=sysaftersales&ctl=refunds&act=index");
        try
        {
            $filter['refunds_id'] = $postdata['refunds_id'];
            $objMdlRefunds = app::get('sysaftersales')->model('refunds');
            $refunds = $objMdlRefunds->getRow('refund_bn,status,aftersales_bn,user_hongbao_id,hongbao_fee,refund_fee,total_price,refunds_type,user_id,shop_id,tid,oid',$filter);

            if( !in_array($refunds['status'], ['3','5','6']) )
            {
                throw new \LogicException(app::get('sysaftersales')->_('当前申请还未审核'));
            }

            $refundsData['refunds_type'] = $refunds['refunds_type'];

            if( $refunds['refunds_type'] != '1' )//退款类型，售后退款
            {
                $refundsData['aftersales_bn'] = $refunds['aftersales_bn'];
            }
            $refundsData['op_id'] = $this->user->get_id();
            $refundsData['return_fee'] = $refundsData['total_price']; //退款总金额，包含红包，方便退款
            $refundsData['refunds_id'] = $postdata['refunds_id']; //sysaftersales/refunds.php主键，方便退款
            $refundsData['payment_id'] = $refundsData['payment_id']; //退款对应原支付单号
            //创建退款单
            $refundId = app::get('sysaftersales')->rpcCall('refund.create', $refundsData);
            if(!$refundId)
            {
                throw new \LogicException(app::get('sysaftersales')->_('退款单创建失败'));
            }

            // 在线原路退款(用什么支付则退到什么地方)
            if($refundsData['rufund_type'] == 'online' && $refundsData['money']>0)
            {
                $apiParams = [
                    'refund_id' => $refundId,
                    'payment_id' => $refundsData['payment_id'],
                    'money' => number_format($refundsData['money'],2,'.',''),
                ];
                $res = app::get('sysaftersales')->rpcCall('payment.trade.refundpay', $apiParams);
                if($res['status']=='progress'){
                    if($res['submit_html'])
                    {
                        return $this->splash('success',null,$msg,'',$res);
                    }
                }
                if($res['status']!='succ'){
                    return $this->splash('error',null,'支付失败或者信息未返回');
                }
            }

            //更改退款申请单
            $apiParams = ['return_fee'=>$refundsData['total_price'], 'refunds_id'=>$postdata['refunds_id']];
            app::get('sysaftersales')->rpcCall('aftersales.refunds.restore', $apiParams);
            $this->adminlog("处理退款[refunds_id:{$postdata['refunds_id']}]", 1);
            return $this->splash('success', null, '退款操作成功!');
        }
        catch(\Exception $e)
        {
            $this->adminlog("处理退款[refunds_id:{$postdata['refunds_id']}]", 0);
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg);
            // $this->end(false,$msg);
        }

        // $this->end('true');
    }
}


