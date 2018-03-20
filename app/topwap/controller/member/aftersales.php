<?php

/**
 * aftersales.php 会员中心售后
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topwap_ctl_member_aftersales extends topwap_ctl_member {

    public $aftersalesReason = [
        '实物不符','质量原因','现在不想购买','商品价格较贵',
        '价格波动','商品缺货','重复下单','订单商品选择有误',
        '支付方式选择有误','收货信息填写有误','支付方式选择有误',
        '发票信息填写有误','其他原因',
    ];

    public $limit = 10;

    public function aftersalesApply()
    {
        $pagedata = [];
        $tid = input::get('tid');
        $oid = input::get('oid');
        // 获取商品信息
        $filter['oid'] = $oid;
        $filter['fields'] = 'item_id,cat_id,end_time,bn,title,price,num,pic_path,spec_nature_info,gift_data';
        $orderInfo = app::get('topwap')->rpcCall('trade.order.get',$filter,'buyer');
        foreach($orderInfo['gift_data'] as $gift)
        {
            if(!$gift['withoutReturn']) $pagedata['giftReturnFlag'] = true;
            if($pagedata['giftReturnFlag']) break;
        }

        //判断是否可售后状态
        $aftersalesEnabled = app::get('topwap')->rpcCall('aftersales.isEnabled',['oid'=>$oid]);
        $orderInfo['refund_enabled'] = $aftersalesEnabled['refund_enabled'];
        $orderInfo['changing_enabled'] = $aftersalesEnabled['changing_enabled'];

        $pagedata['title'] = "申请退货";
        if($orderInfo['refund_enabled'] && !$orderInfo['changing_enabled']){
            $pagedata['title'] = "申请退货";
        }
        if(!$orderInfo['refund_enabled'] && $orderInfo['changing_enabled']){
            $pagedata['title'] = "申请换货";
        }

        $pagedata['tid'] = $tid;
        $pagedata['oid'] = $oid;
        $pagedata['orderInfo'] = $orderInfo;
        $pagedata['reason'] = $this->aftersalesReason;
        $pagedata['status'] = input::get('status');
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');

        return $this->page('topwap/member/aftersales/apply.html' ,$pagedata);
    }

    public function commitAftersalesApply()
    {
        $postdata = input::get();

        $validator = validator::make(
            ['reason'=>$postdata['reason'],'description'=>$postdata['description'],'oid'=>$postdata['oid'],'aftersales_type'=>$postdata['aftersales_type']],
            ['reason'=>'required','description'=>'|max:300','oid'=>'numeric','aftersales_type'=>'required'],
            ['reason'=>'售后申请理由必填!','description'=>'描述不能大于300','oid'=>'订单格式不对','aftersales_type'=>'请选择售后类型']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                return $this->splash('error',null,$error[0]);
            }
        }
        try
        {
            $result = app::get('topwap')->rpcCall('aftersales.apply', input::get(),'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',$url,$msg,true);
        }

        $url = url::action('topwap_ctl_member_aftersales@aftersalesList');

        $msg = '售后申请提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    public function aftersalesList()
    {
        $postdata = input::get();
        $pagedata = $this->__getList($postdata);

        $pagedata['title'] = "退换货记录";
        return $this->page('topwap/member/aftersales/index.html' ,$pagedata);
    }

    public function ajaxAftersalesList()
    {
        $postdata = input::get();
        try {
            $pagedata = $this->__getList($postdata);
            $data['pages'] = $postdata['pages'];
            $data['html'] = view::make('topwap/member/aftersales/list.html',$pagedata)->render();
            $data ['success'] = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }

        return response::json($data);
    }

    public function goAftersalesDetail()
    {
        $params['oid'] = input::get('id');
        $params['fields'] = 'aftersales_bn';
        $result = app::get('topwap')->rpcCall('aftersales.get.bn', $params);
        if(!reset($result))
        {
            redirect::action('topwap_ctl_member_trade@tradeList')->send();exit;
        }
        redirect::action('topwap_ctl_member_aftersales@aftersalesDetail',array('id'=>$result['aftersales_bn']))->send();exit;
    }

    public function aftersalesDetail()
    {
        $params['aftersales_bn'] = input::get('id');
        $params['user_id'] = userAuth::id();
        $tradeFields = 'trade.status,trade.shop_id,trade.receiver_name,trade.user_id,trade.receiver_state,trade.receiver_city,trade.receiver_district,trade.receiver_address,trade.receiver_mobile,trade.receiver_phone';
        $params['fields'] = 'aftersales_bn,aftersales_type,modified_time,reason,sendback_data,sendconfirm_data,description,shop_explanation,admin_explanation,evidence_pic,created_time,oid,tid,num,progress,status,sku,gift_data,'.$tradeFields;
        $result = app::get('topwap')->rpcCall('aftersales.get', $params);
        $result['evidence_pic'] = $result['evidence_pic'] ? explode(',',$result['evidence_pic']) : null;
        $result['sendback_data'] = $result['sendback_data'] ? unserialize($result['sendback_data']) : null;
        $result['sendconfirm_data'] = $result['sendconfirm_data'] ? unserialize($result['sendconfirm_data']) : null;

        $pagedata['info'] = $result;

        $pagedata['tracking'] = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $pagedata['title'] = "退换货详情";
        $pagedata ['defaultImageId'] = kernel::single('image_data_image')->getImageSetting('item');
        return $this->page('topwap/member/aftersales/detail.html' ,$pagedata);
    }

    // 填写售后回寄物流信息
    public function createAfterlogistics()
    {
        $pagedata['aftersales_bn'] = input::get('id');
        //快递公司代码
        $params['fields'] = "corp_code,corp_name";
        $corpData = app::get('topwap')->rpcCall('logistics.dlycorp.get.list',$params);
        $pagedata['corpData'] = $corpData['data'];
        $pagedata['title'] = app::get('topwap')->_('填写物流信息');

        return $this->page('topwap/member/aftersales/logistics_select.html' ,$pagedata);
    }

    // 选择物流后
    public function ajaxcreateAfterlogistics()
    {
        $postdata = input::get();
        if(!$postdata['corp_code'])
        {
            return $this->splash('error',null,"请选择物流公司",true);
        }

        $pagedata['aftersales_bn'] = input::get('id');
        $pagedata['corp_code'] = $postdata['corp_code'];
        $pagedata['title'] = app::get('topwap')->_('填写物流信息');
        $data['html'] = view::make('topwap/member/aftersales/logistics_from.html',$pagedata)->render();
        $data ['success'] = true;

        return response::json($data);
    }

    // 查看物流信息
    public function seeAfterlogistics()
    {
        $params['aftersales_bn'] = input::get('id');
        $type = input::get('type');
        $params['user_id'] = userAuth::id();
        $params['fields'] = 'aftersales_bn,aftersales_type,sendback_data,sendconfirm_data,progress';
        $result = app::get('topwap')->rpcCall('aftersales.get', $params);

        $result['sendback_data'] = $result['sendback_data'] ? unserialize($result['sendback_data']) : null;
        $result['sendconfirm_data'] = $result['sendconfirm_data'] ? unserialize($result['sendconfirm_data']) : null;

        $tracking = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $pagedata['title'] = app::get('topwap')->_('寄送物流信息');
        if( $result['sendback_data']['corp_code']  && $type=='user')
        {
            $logiData = explode('-',$result['sendback_data']['corp_code']);
            $result['sendback_data']['corp_code'] = $logiData[0];
            $result['sendback_data']['logi_name'] = $logiData[1];
            if($tracking && $tracking =='true' && $result['sendback_data']['corp_code'] != "other")
            {
                $send_back['logi_no'] = $result['sendback_data']['logi_no'];
                $send_back['corp_code'] = $logiData[0];
                $log_info = app::get('topwap')->rpcCall('logistics.tracking.get.hqepay',$send_back);
                krsort($log_info['tracker']);

                $result['track'] = $log_info;
            }

            $pagedata['logi_no'] = $result['sendback_data']['logi_no'];
            $pagedata['logi_name'] = $result['sendback_data']['logi_name'];
            $pagedata['title'] = app::get('topwap')->_('会员').$pagedata['title'];
        }

        if( $result['sendconfirm_data']['corp_code'] && $type=='shop')
        {
            if($tracking && $tracking =='true' && $result['sendconfirm_data']['corp_code'] != "other")
            {
                $logiData = explode('-',$result['sendconfirm_data']['corp_code']);
                $result['sendconfirm_data']['corp_code'] = $logiData[0];
                $result['sendconfirm_data']['logi_name'] = $logiData[1];
                $send_back['logi_no'] = $result['sendconfirm_data']['logi_no'];
                $send_back['corp_code'] = $logiData[0];
                $log_info = app::get('topwap')->rpcCall('logistics.tracking.get.hqepay',$send_back);
                krsort($log_info['tracker']);
                $result['track'] = $log_info;

            }
            $pagedata['logi_no'] = $result['sendconfirm_data']['logi_no'];
            $pagedata['logi_name'] = $result['sendconfirm_data']['logi_name'];
            $pagedata['title'] = app::get('topwap')->_('商家').$pagedata['title'];
        }

        $pagedata['info'] = $result;
        return $this->page('topwap/member/aftersales/logistics_deatil.html' ,$pagedata);
    }

    // 填写物流信息
    public function sendback()
    {
        $postdata = input::get();

        $postdata['user_id'] = userAuth::id();
        if($postdata['corp_code'] == "other" && !$postdata['logi_name'])
        {
            return $this->splash('error',"","其他物流公司不能为空",true);
        }

        try
        {
            //验证字段
            $validator = validator::make(
                [$postdata['corp_code'],$postdata['logi_no'],$postdata['mobile'],$postdata['receiver_address']],
                ['required','required|min:6|max:20','required|mobile','required'],
                ['物流公司不能为空!','物流单号不能为空!|运单号不能小于6|运单号不能大于20','收货手机号不能为空!|收货手机号格式不对!','收货地址不能为空!']
            );
            $validator->newFails();

            $result = app::get('topwap')->rpcCall('aftersales.send.back', $postdata,'buyer');
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error',null,$msg,true);
        }

        $url = url::action('topwap_ctl_member_aftersales@aftersalesList');

        $msg = '回寄物流信息提交成功';
        return $this->splash('success',$url,$msg,true);
    }

    private function __getList($postdata)
    {
        $params['user_id'] = userAuth::id();
        $params['page_no'] = isset($postdata['pages'])?intval($postdata['pages']):1;
        $params['page_size'] = $this->limit;
        $params['fields'] = 'shop_id,aftersales_bn,aftersales_type,created_time,oid,tid,num,progress,status,sku,gift_data';
        $result = app::get('topwap')->rpcCall('aftersales.list.get', $params,'buyer');
        $result['list'] = $this->__proResult($result);

        $pagedata['list'] = $result['list'];
        $pagedata['defaultImageId']= kernel::single('image_data_image')->getImageSetting('item');
        $pagedata['pagers'] = $this->__pages($postdata['pages'],$postdata,$result['total_found']);
        return $pagedata;
    }

    /**
     * 分页处理
     * @param int $current 当前页
     *
     * @return $pagers
     */
    private function __pages($current,$filter,$count)
    {
        //处理翻页数据
        $current = ($current && $current <= 100 ) ? $current : 1;

        if( $count > 0 ) $totalPage = ceil($count/$this->limit);
        $pagers = array(
            'link'=>'',
            'current'=>intval($current),
            'total'=>intval($totalPage),
        );
        return $pagers;
    }

    /**
     * 处理返回数据
     * @param array $result
     *
     * @return array
     * */

    private function __proResult($result)
    {
        $oids = array_column($result['list'], 'oid');
        $oids= array_unique($oids);
        $tmpList = array();

        foreach ($oids as $ov)
        {
            foreach ($result['list'] as $val)
            {
                if($ov == $val['oid'])
                {
                    $tmpList[$ov][] = $val;
                }
            }
        }

        // 添加投诉状态
        foreach ($tmpList as &$tval)
        {
            if($tval[0]['progress'] == 3)
            {
                if($tval[0]['sku']['complaints_status'] == 'NOT_COMPLAINTS')
                {
                    $tval[0]['arge'] = 1;
                }
            }
        }

        // 取得结果
        $proList = array();
        foreach ($tmpList as $vv)
        {
            $proList = array_merge($proList, $vv);
        }

        // 根据售后发起时间逆向排序
        $tmpResultList = array();
        foreach ($proList as $pval)
        {
            $tmpResultList[$pval['created_time']] = $pval;
        }
        krsort($tmpResultList);

        return $tmpResultList;
    }

}

