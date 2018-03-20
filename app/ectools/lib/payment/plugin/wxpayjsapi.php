<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * alipay支付宝手机支付接口
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_wxpayjsapi extends ectools_payment_app implements ectools_interface_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '微信支付JSAPI';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = '微信支付新接口';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'wxpayjsapi';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'wxpayjsapi';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '微信支付JSAPI接口';
    /**
     * @var string 货币名称
     */
    public $curname = 'CNY';
    /**
     * @var string 当前支付方式的版本号
     */
    public $ver = '1.0';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'iswap';

    /**
     * @var array 扩展参数
     */
    public $supportCurrency = array("CNY"=>"01");

    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);

        $this->notify_url = url::to('wap/wxpayjsapi.html');
        $this->unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $this->refund_url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $this->submit_charset = 'UTF-8';
        $certdir = DATA_DIR . '/cert/payment_plugin_wxpayjsapi/';
        $this->SSLCERT_PATH = $certdir.trim($this->getConf('apiclient_cert', __CLASS__));
        $this->SSLKEY_PATH = $certdir.trim($this->getConf('apiclient_key', __CLASS__));
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        $regIp = isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$_SERVER['HTTP_HOST'];
        return '<img src="' . app::get('weixin')->res_url . '/payments/images/WXPAY.jpg"><br /><b style="font-family:verdana;font-size:13px;padding:3px;color:#000"><br>微信支付(JSAPI V3.3.6)是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。</b>
            <br>如果遇到支付问题，请访问：<a href="javascript:void(0)" onclick="top.location = '."'http://bbs.ec-os.net/read.php?tid=1007'".'">http://bbs.ec-os.net/read.php?tid=1007</a>';
    }

     /**
     * 后台配置参数设置
     * @param null
     * @return array 配置参数列表
     */
    public function setting(){

        return array(
            'pay_name'=>array(
                'title'=>app::get('ectools')->_('支付方式名称'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'appId'=>array(
                'title'=>app::get('ectools')->_('appId'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'Mchid'=>array(
                'title'=>app::get('ectools')->_('Mchid'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'Key'=>array(
                'title'=>app::get('ectools')->_('Key'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'Appsecret'=>array(
                'title'=>app::get('ectools')->_('Appsecret'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'apiclient_cert'=>array(
                'title'=>app::get('ectools')->_('证书'),
                'type'=>'file',
                'validate_type' => 'required',
                'label'=>app::get('ectools')->_('官方下载文件名是apiclient_cert.pem'),
            ),
            'apiclient_key'=>array(
                'title'=>app::get('ectools')->_('证书密钥'),
                'type'=>'file',
                'validate_type' => 'required',
                'label'=>app::get('ectools')->_('官方下载文件名是apiclient_key.pem'),
            ),
            'order_by' =>array(
                'title'=>app::get('ectools')->_('排序'),
                'type'=>'string',
                'label'=>app::get('ectools')->_('整数值越小,显示越靠前,默认值为1'),
            ),
            'pay_desc'=>array(
                'title'=>app::get('ectools')->_('描述'),
                'type'=>'html',
                'includeBase' => true,
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
                'name' => 'status',
            ),
            'def_payment'=>array(
                'title'=>app::get('ectools')->_('设为默认支付方式'),
                'type' =>'radio',
                'options'=>array('0'=>app::get('ectools')->_('否'),'1'=>app::get('ectools')->_('是')),
                'name' =>'def_payment',
            ),
        );
    }

    /**
     * 前台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function intro(){
        return app::get('ectools')->_('微信支付是由腾讯公司知名移动社交通讯软件微信及第三方支付平台财付通联合推出的移动支付创新产品，旨在为广大微信用户及商户提供更优质的支付服务，微信的支付和安全系统由腾讯财付通提供支持。财付通是持有互联网支付牌照并具备完备的安全体系的第三方支付平台。');
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment)
    {
        $appid  = trim($this->getConf('appId', __CLASS__));
        $mch_id = trim($this->getConf('Mchid', __CLASS__));
        $key    = trim($this->getConf('Key',   __CLASS__));

        $parameters = array(
            'appid'            => strval($appid),
            'openid'           => strval(input::get('openid')),
            'body'             => strval($payment['item_title'].'...'),
            'out_trade_no'     => strval( $payment['payment_id'] ),
            'total_fee'        => bcmul($payment['cur_money'], 100, 0),
            'notify_url'       => strval( $this->notify_url ),
            'trade_type'       => 'JSAPI',
            'mch_id'           => strval($mch_id),
            'nonce_str'        => ectools_payment_plugin_wxpay_util::create_noncestr(),
          //'spbill_create_ip' => strval( $_SERVER['REMOTE_ADDR'] ),
            //android版本的微信6.1会报服务器忙的错误，这里临时改为服务器ip（文档为客户端ip），可以规避该错误
            'spbill_create_ip' => strval( $_SERVER['SERVER_ADDR'] ),
        );
        $parameters['sign'] = $this->getSign($parameters, $key);
        $xml                = $this->arrayToXml($parameters);
        logger::info('wxpayjsapi: post to weixin for prepay_id:'.var_export($xml, 1));
        $response = client::post($this->unifiedorder_url, ['body' => $xml])->getBody();// 获取guzzle返回的值的body部分
        logger::info('wxpayjsapi response info from weixin for prepay_id:'.var_export((string)$response, 1));
        $result             = $this->xmlToArray($response);
        $prepay_id          = $result['prepay_id'];

        if($prepay_id == '')
        {
            echo $this->get_error_html($result['return_msg']);
        }

        $data = [
            'appId'     => $appid,  
            'timeStamp' => strval(time()),
            'nonceStr'  => ectools_payment_plugin_wxpay_util::create_noncestr(),
            'package'   => 'prepay_id='.$prepay_id,
            'signType'  => 'MD5',
        ];
        $data['paySign'] = $this->getSign($data, $key);

        echo $this->get_html($payment['payment_id'], $data);exit;
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    function callback(&$in)
    {
        $mch_id = trim($this->getConf('Mchid', __CLASS__));
        $key    = trim($this->getConf('Key',   __CLASS__));
        $in = $in['weixin_postdata'];

        if( $in['return_code'] == 'SUCCESS' )
        {
            if( $in['sign'] == $this->getSign($in, $key))
            {
                $money = ecmath::number_multiple(array($in['total_fee'], 0.01));
                $ret['payment_id' ] = $in['out_trade_no'];
                $ret['account']     = $mch_id;
                $ret['bank']        = app::get('ectools')->_('微信支付JSAPI');
                $ret['pay_account'] = $in['openid'];
                $ret['currency']    = 'CNY';
                $ret['money']       = $money;
                $ret['paycost']     = '0.00';
                $ret['cur_money']   = $money;
                $ret['trade_no']    = $in['transaction_id'];
                $ret['t_payed']     = strtotime($in['time_end']) ? strtotime($in['time_end']) : time();
                $ret['pay_app_id']  = "wxpayjsapi";
                $ret['pay_type']    = 'online';
                $ret['memo']        = $in['attach'];
                $ret['status']      = 'succ';

            }
            else
            {
                $ret['status'] = 'failed';
            }
        }
        else
        {
            $ret['status'] = 'failed';
        }
        return $ret;
    }

    /**
     * 支付成功回打支付成功信息给支付网关
     */
    function ret_result($paymentId)
    {
        $ret = array('return_code'=>'SUCCESS', 'return_msg'=>'');
        $ret = $this->arrayToXml($ret);
        echo $ret;exit;
    }

    /**
     * 校验方法
     * @param null
     * @return boolean
     */
    public function is_fields_valiad()
    {
        return true;
    }

    /**
     * 生成支付表单 - 自动提交
     * @params null
     * @return null
     */
    public function gen_form()
    {
        return '';
    }

    protected function get_error_html($info)
    {
        if($info == '')
        {
            $info = '系统繁忙，请选择其它支付方式或联系客服。';
        }
        header("Content-Type: text/html;charset=".$this->submit_charset);
        $html = '
            <html>
                <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
                <title>微信安全支付</title>
                <script language="javascript">
                    alert("'.$info.'");
                </script>
            </html>
            ';

        return $html;
    }


    protected function get_html($payment_id, $data){
        header("Content-Type: text/html;charset=".$this->submit_charset);
        $return_url = url::action('topwap_ctl_paycenter@finish', ['payment_id'=>$payment_id]);
        $strHtml = '
                <html>
                    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
                    <title>微信安全支付</title>
                    <script language="javascript">
                                //调用微信JS api 支付
                                function jsApiCall()
                                {
                                    WeixinJSBridge.invoke(
                                        "getBrandWCPayRequest",
                                        '.json_encode($data).',
                                        function(res){
                                            // WeixinJSBridge.log(res.err_msg);
                                            if(res.err_msg=="get_brand_wcpay_requst:ok"){
                                                window.location.href = "' . $return_url . '";
                                            }else{
                                            //  alert(res.err_msg);
                                                window.location.href = "' . $return_url . '";
                                            }
                                            // alert(res.err_code+res.err_desc+res.err_msg);
                                        }
                                    );
                                }

                                function callpay()
                                {
                                    if (typeof WeixinJSBridge == "undefined"){
                                        if( document.addEventListener ){
                                            document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);
                                        }else if (document.attachEvent){
                                            document.attachEvent("WeixinJSBridgeReady", jsApiCall);
                                            document.attachEvent("onWeixinJSBridgeReady", jsApiCall);
                                        }
                                    }else{
                                        jsApiCall();
                                    }
                                }
                                callpay();
                    </script>
                    <body>
                 <button type="button" onclick="callpay()" style="display:none;">微信支付</button>
                 </body>
                </html>
            ';
        return $strHtml;
    }

//↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓公共函数部分↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    /**
     *  作用：将xml转为array
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     *  作用：array转xml
     */
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                $xml.="<".$key.">".$val."</".$key.">";
             }
             else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     *  作用：格式化参数并生成签名
     */
    public function getSign($Parameters, $key)
    {
        ksort($Parameters); //签名步骤一：按字典序排序参数
        $buff = "";
        foreach ($Parameters as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v))
            {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $String = trim($buff, "&");
        $String = $String."&key=".$key; //签名步骤二：在string后加入KEY
        $String = md5($String); //签名步骤三：MD5加密
        $result = strtoupper($String); //签名步骤四：所有字符转为大写
        return $result;
    }

//↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑公共函数部分↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


/* 以下为退款代码 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ */

    /**
     * 提交退款支付信息的接口
     * 微信退款不需要异步返回，调用的时候直接返回处理信息，不需要refundcallback方法了
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dorefund($payment)
    {
        $appid  = trim($this->getConf('appId', __CLASS__));
        $mch_id = trim($this->getConf('Mchid', __CLASS__));
        $key    = trim($this->getConf('Key',   __CLASS__));

        $parameters = array(
            'appid'           => strval($appid),
            'mch_id'          => strval($mch_id),
            'nonce_str'       => ectools_payment_plugin_wxpay_util::create_noncestr(),
            'sign_type'       => 'MD5',
            'transaction_id'  => $payment['trade_no'],
            // 'out_trade_no'    => strval( $payment['payment_id'] ),
            'out_refund_no'   => strval( $payment['refund_id'] ),
            'total_fee'       => bcmul($payment['total_fee'], 100, 0),
            'refund_fee'      => bcmul($payment['refund_fee'], 100, 0),
            'refund_fee_type' => 'CNY',
            'op_user_id'      => strval($appid),
        );
        $parameters['sign'] = $this->getSign($parameters, $key);
        $xml      = $this->arrayToXml($parameters);
        $response = client::post($this->refund_url, ['verify'=>true,'cert'=>$this->SSLCERT_PATH,'ssl_key'=>$this->SSLKEY_PATH,'body' => $xml])->getBody();
        $result   = $this->xmlToArray($response);
        logger::info('微信退款返回信息：'.var_export($result,1));

        $ret['refund_id'] = $payment['refund_id'];
        $ret['trade_no'] = $payment['trade_no'];
        // $ret['third_transaction_id'] = $payment['refund_id'];//退款的第三方退款交易号
        if(strtoupper($result['return_code']) == 'SUCCESS')
        {
            $ret['status'] = 'succ';
        }
        else
        {
            $ret['status'] = 'failed';
        }
        return $ret;

    }

/* 以上为退款代码 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ */


}
