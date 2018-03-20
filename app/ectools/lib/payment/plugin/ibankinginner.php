<?php
final class ectools_payment_plugin_ibankinginner extends ectools_payment_app implements ectools_interface_payment_app
{
    //支付方式名称
    public $name = '网银在线(内卡)';

    //支付方式接口名称
    public $app_name = '网银在线内卡支付';

    //支付方式key（系统使用）
    public $app_key = 'ibankinginner';

    //中心化统一管理key（系统使用）
    public $app_rpc_key = 'ibankinginner';

    //显示名称
    public $display_name = '网银在线（内卡）';

    //货币名称
    public $curname = 'CNY';

    //版本号（系统使用）
    public $ver = '1.0';

    //支付方式是pc端还是wap端
    public $platform = 'ispc';

    //支持币种
    public $supportCurrency = array('CNY'=>'CNY');

    public $gateway="https://pay3.chinabank.com.cn/PayGate?encoding=UTF-8";



    //构造函数
    public function __construct($app)
    {
        parent::__construct($app);
        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/'.$this->app->app_id.'/ectools_payment_plugin_ibankinginner_server','callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
        {
            $this->notify_url = str_replace('http://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "http://" . $this->notify_url;
        }
        else
        {
            $this->notify_url = str_replace('https://','',$this->notify_url);
            $this->notify_url = preg_replace("|/+|","/", $this->notify_url);
            $this->notify_url = "https://" . $this->notify_url;
        }
        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_ibankinginner', 'callback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->callback_url, $matches))
        {
            $this->callback_url = str_replace('http://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "http://" . $this->callback_url;
        }
        else
        {
            $this->callback_url = str_replace('https://','',$this->callback_url);
            $this->callback_url = preg_replace("|/+|","/", $this->callback_url);
            $this->callback_url = "https://" . $this->callback_url;
        }
        $this->submit_url = 'https://pay3.chinabank.com.cn/PayGate?encoding=UTF-8';
        //退款异步返回地址
        $this->notify_url_refund = kernel::openapi_url('openapi.ectools_payment/parse/'.$this->app->app_id.'/ectools_payment_plugin_ibankinginner_server','refundcallback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url_refund, $matches))
        {
            $this->notify_url_refund = str_replace('http://','',$this->notify_url_refund);
            $this->notify_url_refund = preg_replace("|/+|","/", $this->notify_url_refund);
            $this->notify_url_refund = "http://" . $this->notify_url_refund;
        }
        else
        {
            $this->notify_url_refund = str_replace('https://','',$this->notify_url_refund);
            $this->notify_url_refund = preg_replace("|/+|","/", $this->notify_url_refund);
            $this->notify_url_refund = "https://" . $this->notify_url_refund;
        }
        $this->refund_submit_url = "https://tmapi.jdpay.com/jd.htm";
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8';
    }

    //后台支付方式介绍
    public function admin_intro()
    {
        return app::get('ectools')->_( '<div><p >网银在线（北京）科技有限公司（以下简称网银在线）为京东商城（www.jd.com）全资子公司，是国内领先的电子支付解决方案提供商，专注于为各行业提供安全、便捷的综合电子支付服务。网银在线成立于2003年，现有员工200余人，由具有丰富的金融行业经验和互联网运营经验的专业团队组成，致力于通过创新型的金融服务，支持现代服务业的发展。凭借丰富的产品线、卓越的创新能力，网银在线受到各级政府部门和银行金融机构的高度重视和认可，于2011年5月3日首批荣获央行《支付业务许可证》，并任中国支付清算协会理事单位。</p><br /></div>');
    }

    public function intro()
    {
        return app::get('ectools')->_( '<div><p >网银在线（北京）科技有限公司（以下简称网银在线）为京东商城（www.jd.com）全资子公司，是国内领先的电子支付解决方案提供商，专注于为各行业提供安全、便捷的综合电子支付服务。网银在线成立于2003年，现有员工200余人，由具有丰富的金融行业经验和互联网运营经验的专业团队组成，致力于通过创新型的金融服务，支持现代服务业的发展。凭借丰富的产品线、卓越的创新能力，网银在线受到各级政府部门和银行金融机构的高度重视和认可，于2011年5月3日首批荣获央行《支付业务许可证》，并任中国支付清算协会理事单位。</p><br /></div>');
    }

    //后台配置
    public function setting()
    {
        return array(
                    'pay_name'=>array(
                        'title'=>app::get('ectools')->_('支付方式名称'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'mer_id'=>array(
                        'title'=>app::get('ectools')->_('商户号'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'mer_key'=>array(
                        'title'=>app::get('ectools')->_('交易安全校密钥(key)'),
                        'type'=>'string',
						'validate_type' => 'required',
                    ),
                    'mer_refund_key'=>array(
                        'title'=>app::get('ectools')->_('退款安全校秘钥(refundkey)'),
                        'type'=>'string',
                    ),
                    'order_by' =>array(
                        'title'=>app::get('ectools')->_('排序'),
                        'type'=>'string',
                        'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
                    ),
      //               'support_cur'=>array(
      //                   'title'=>app::get('ectools')->_('支持币种'),
      //                   'type'=>'text hidden cur',
						// 'options'=>$this->arrayCurrencyOptions,
      //               ),
                    'pay_fee'=>array(
                        'title'=>app::get('ectools')->_('交易费率'),
                        'type'=>'pecentage',
						'validate_type' => 'number',
                    ),
//                     'pay_brief'=>array(
//                         'title'=>app::get('ectools')->_('支付方式简介'),
//                          'type'=>'textarea',
//                     ),
                    'pay_desc'=>array(
                        'title'=>app::get('ectools')->_('描述'),
                        'type'=>'html',
						'includeBase'=>true,
                    ),
					'pay_type'=>array(
						 'title'=>app::get('ectools')->_('支付类型(是否在线支付)'),
						 'type'=>'radio',
                         'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
						 'name' => 'pay_type',
					),
					'status'=>array(
						'title'=>app::get('ectools')->_('是否开启此支付方式'),
						'type'=>'radio',
						'options'=>array('false'=>app::get('ectools')->_('否'),'true'=>app::get('ectools')->_('是')),
						'name'=>'status',
					),
                    'def_payment'=>array(
                        'title'=>app::get('ectools')->_('设为默认支付方式'),
                        'type' =>'radio',
                        'options'=>array('0'=>app::get('ectools')->_('否'),'1'=>app::get('ectools')->_('是')),
                        'name' =>'def_payment',
                    ),
                );
    }

    public function dopay($payment)
    {
        $mer_id = trim($this->getConf('mer_id', __CLASS__));
        $mer_key = trim($this->getConf('mer_key', __CLASS__));
        $payment['shopName'] = app::get('site')->getConf('site.name');
        $payment['cur_money'] = number_format($payment['cur_money'],2,'.','');
        $pay = array(
            'v_mid'=>$mer_id,
            'v_oid'=>$payment['payment_id'],
            'v_amount'=>$payment['cur_money'],
            'v_moneytype'=>$this->curname,
            'v_url'=>$this->callback_url,
            'remark1'=>$payment['shopName'],
            'remark2'=>'[url:='.$this->notify_url.']',
        );
        $pay['v_md5info'] = $this->make_sign($pay, $mer_key);
        foreach($pay as $k=>$v)
        {
            $this->add_field($k, $v);
        }
        echo $this->get_html();exit;
    }

    //验证方法
    public function is_fields_valiad(){
        return true;
    }

    public function callback(&$recv)
    {
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $mer_key = $this->getConf('mer_key', __CLASS__);

        if($this->is_return_valiad($recv, $mer_key))
        {
            $ret['payment_id'] = $recv['v_oid'];
            $ret['account'] = $mer_id;
            $ret['bank'] = app::get('ectools')->_('网银在线');
            $ret['pay_account'] = app::get('ectools')->_('付款帐号');
            $ret['currency'] = $recv['v_moneytype'];
            $ret['money'] = $recv['v_amount'];
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $recv['v_amount'];
            $ret['trade_no'] = $recv['v_oid'];
            $ret['t_payed'] = time();
            $ret['pay_app_id'] = 'ibankinginner';
            $ret['pay_type'] = 'online';
            $ret['memo'] = 'memo';
            if($recv['v_pstatus'] == '20')
            {

                $ret['status'] = 'succ';
            }
            else
            {
                $ret['status'] = 'failed';
            }
        }else{
            $ret['message'] = 'Invalid Sign';
            $ret['status'] = 'invalid';
        }
        return $ret;
    }

    public function gen_form()
    {
        return '';
    }

    private function is_return_valiad($recv, $key)
    {
        $sign = $this->make_return_sign($recv, $key);
        return ($sign == $recv['v_md5str']) ? true : false;
    }

    private function make_return_sign($recv, $key)
    {
        $linkstring = $recv['v_oid'];
        $linkstring .= $recv['v_pstatus'];
        $linkstring .= $recv['v_amount'];
        $linkstring .= $recv['v_moneytype'];
        $linkstring .= $key;
        return strtoupper(md5($linkstring));
    }

    private function make_sign($payment, $mer_key)
    {
        $linkstring = $payment['v_amount'];
        $linkstring .= $payment['v_moneytype'];
        $linkstring .= $payment['v_oid'];
        $linkstring .= $payment['v_mid'];
        $linkstring .= $payment['v_url'];
        $linkstring .= $mer_key;
        return strtoupper(md5($linkstring));
    }

/* 以下为退款代码 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ */

    /**
     * 提交退款支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dorefund($payment){
        $mer_id = trim($this->getConf('mer_id', __CLASS__));
        $mer_refund_key = trim($this->getConf('mer_refund_key', __CLASS__));
        if(!$mer_id || !$mer_refund_key){
            throw new Exception(app::get('ectools')->_('请检查后台网银在线支付方式配置！'));
        }
        $params  = array(
            'VERSION' => "1.0.0",  //版本号 1.0.0
            'MERCHANT' => $mer_id, //商户号
            'TYPE' => "R",         // 交易类型，退款：R
            'TRADE' => $payment['refund_id'],  //商户订单号
            'ORDER' => $payment['trade_no'],  //原商户订单号
            'AMOUNT' => bcmul($payment['refund_fee'], 100, 0),  //交易金额
            'CURRENCY' => "CNY",//交易币种
            // 'DATETIME' => date('Y-m-d H:i:s'),
            'NOTICE' => $this->notify_url_refund,
        );

        $requestData = base64_encode(json_encode($params));

        $sign = md5($requestData."".$mer_refund_key);

        $requestArgs['CHAR'] = "UTF-8";
        $requestArgs['DATA'] = $requestData;
        $requestArgs['SIGN'] = $sign;

        $response = client::post($this->refund_submit_url, ['verify'=>false, 'body'=>$requestArgs])->getBody();
        logger::info('response：'.var_export($response,1));


        $responseData = $this->convertStringToArray($response);
        $result = json_decode(base64_decode($responseData['DATA']),true);

        logger::info('网银在线退款返回信息格式化：'.var_export($result,1));

        $ret['refund_id'] = $payment['refund_id'];
        $ret['trade_no'] = $payment['trade_no'];
        $ret['refund_fee'] = $payment['refund_fee'];
        if($result['STATUS'] =='S'){
            $ret['status'] = 'succ';
        }elseif($result['STATUS'] =='F'){
            $ret['status'] = 'failed';
        }

        return $ret;

    }

    /**
     * 处理第三方支付方式退款返回的信息
     * @param array 第三方支付方式返回的信息
     * @return mixed
     */
    public function refundcallback(&$recv){
        logger::info('refundcallback'.var_export($recv,1));
        if($recv['STATUS'] =='S'){
            $ret['refund_id'] = $recv['TRADE'];
            $ret['trade_no'] = $recv['OEDER'];
            $ret['refund_fee'] = number_format(($payment['AMOUNT']/100),2,".","");
            $ret['status'] = 'REFUND_SUCCESS';
        }

        return $ret;

    }

    /**
     * CHAR=value1&DATA=value2&SIGN=value3转array
     * @param $response CHAR=value1&DATA=value2&SIGN=value3的字符串
     */
    public function convertStringToArray($response)
    {

        $arrStr = [];
        $arrSplits = [];
        $arrQueryStrs = [];

        if ($response)
        {
            $arrStr = explode("&", $response);
            foreach ($arrStr as $str)
            {
                $arrSplits = explode("=", $str);
                $index = strpos($str,"=");
                if(count($arrSplits)<2){
                    continue;
                }else {
                    $arrQueryStrs[substr($str, 0, $index)] = substr($str, $index+1);
                }
            }
        }
        return $arrQueryStrs;
    }

/* 以上为退款代码 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ */
}

