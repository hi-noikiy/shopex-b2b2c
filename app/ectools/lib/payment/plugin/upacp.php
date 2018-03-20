<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * upacp银联（unipay）
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment.plugin
 */
final class ectools_payment_plugin_upacp extends ectools_payment_app implements ectools_interface_payment_app {

    /**
     * @var string 支付方式名称
     */
    public $name = '中国银联网关支付(pc)';
    /**
     * @var string 支付方式接口名称
     */
    public $app_name = 'upacp';
     /**
     * @var string 支付方式key
     */
    public $app_key = 'upacp';
    /**
     * @var string 中心化统一的key
     */
    public $app_rpc_key = 'upacp';
    /**
     * @var string 统一显示的名称
     */
    public $display_name = '中国银联网关支付(pc)';
    /**
     * @var string 货币名称
     */
    public $curname = 'CNY';
    /**
     * @var string 当前支付方式所支持的平台
     */
    public $platform = 'ispc';

    public $supportCurrency = array("CNY"=>"01");

    // 前台 测试 地址
    // public $sdk_front_trans_url = 'https://101.231.204.80:5000/gateway/api/frontTransReq.do';
    // 前台 正式 地址
    public $sdk_front_trans_url = 'https://gateway.95516.com/gateway/api/frontTransReq.do';

    // 后台退款 测试 地址
    // public $sdk_back_trans_url = 'https://101.231.204.80:5000/gateway/api/backTransReq.do';
    // 后台退款 正式 地址
    public $sdk_back_trans_url = 'https://gateway.95516.com/gateway/api/backTransReq.do';

    public $version = '5.0.0';


    /**
     * 构造方法
     * @param null
     * @return boolean
     */
    public function __construct($app){
        parent::__construct($app);
        $this->notify_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_upacp', 'callback');
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

        $this->callback_url = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_upacp', 'callback');
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
        // 退款异步返回地址
        $this->notify_url_refund = kernel::openapi_url('openapi.ectools_payment/parse/' . $this->app->app_id . '/ectools_payment_plugin_upacp', 'refundcallback');
        if (preg_match("/^(http):\/\/?([^\/]+)/i", $this->notify_url, $matches))
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
        $this->submit_method = 'POST';
        $this->submit_charset = 'utf-8'; //要小写
    }

    /**
     * 后台支付方式列表关于此支付方式的简介
     * @param null
     * @return string 简介内容
     */
    public function admin_intro(){
        return '中国银联支付（UnionPay）是银联电子支付服务有限公司主要从事以互联网等新兴渠道为基础的网上支付。（适用于中国银联的新签约用户）';
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
            'mer_id'=>array(
                'title'=>app::get('ectools')->_('商户代码'),
                'type'=>'string',
                'validate_type' => 'required',
            ),
            'sdk_sign_cert_path'=>array(
                'title'=>app::get('ectools')->_('签名证书'),
                'type'=>'file',
                'validate_type' => 'required',
                'label'=>app::get('ectools')->_('文件后缀名为.pfx'),
            ),
            'sign_cert_pwd'=>array(
                'title'=>app::get('ectools')->_('签名证书密码'),
                'type'=>'string',
                'validate_type' => 'required',
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
        return '中国银联支付（UnionPay）是银联电子支付服务有限公司主要从事以互联网等新兴渠道为基础的网上支付。';
    }

    /**
     * 提交支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dopay($payment){
        // 初始化日志
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $cert_dir = DATA_DIR . '/cert/payment_plugin_upacp/';
        $sdk_sign_cert_path = $cert_dir.trim($this->getConf('sdk_sign_cert_path', __CLASS__));
        $sdk_sign_cert_pwd  = $this->getConf('sign_cert_pwd', __CLASS__);
        $params = array(
                'version' => '5.0.0',               //版本号
                'encoding' => 'utf-8',              //编码方式
                'certId' => $this->getCertId($sdk_sign_cert_path, $sdk_sign_cert_pwd),           //证书ID
                'txnType' => '01',              //交易类型
                'txnSubType' => '01',               //交易子类
                'bizType' => '000201',              //业务类型
                'frontUrl' =>  $this->notify_url,        //前台通知地址
                'backUrl' => $this->callback_url,       //后台通知地址
                'signMethod' => '01',       //签名方法
                'channelType' => '07',      //渠道类型，07-PC，08-手机
                'accessType' => '0',        //接入类型
                'merId' => $mer_id,               //商户代码，请改自己的测试商户号
                'orderId' => $payment['payment_id'],    //商户订单号
                'txnTime' => date('YmdHis'),    //订单发送时间
                'txnAmt' => number_format($payment['cur_money'],2,".","")*100,      //交易金额，单位分
                'currencyCode' => '156',    //交易币种
                'defaultPayType' => '0001', //默认支付方式
                //'orderDesc' => '订单描述',  //订单描述，网关支付和wap支付暂时不起作用
                'reqReserved' =>' 透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
                );
        $this->sign ( $params, $sdk_sign_cert_path, $sdk_sign_cert_pwd);
        $front_uri = $this->sdk_front_trans_url;
        echo $this->create_html ( $params, $front_uri);
        exit;
    }

    public function getCertId($cert_path, $cert_pwd) {
        $pkcs12certdata = file_get_contents ( $cert_path );
        openssl_pkcs12_read ( $pkcs12certdata, $certs, $cert_pwd );
        $x509data = $certs ['cert'];
        openssl_x509_read ( $x509data );
        $certdata = openssl_x509_parse ( $x509data );
        $cert_id = $certdata ['serialNumber'];
        return $cert_id;
    }

    /**
     * 签名
     *
     * @param String $params_str
     */
    public function sign(&$params, $cert_path, $cert_pwd) {

        if(isset($params['transTempUrl'])){
            unset($params['transTempUrl']);
        }
        // 转换成key=val&串
        $params_str = $this->coverParamsToString ( $params );
        $params_sha1x16 = sha1 ( $params_str, FALSE );
        // 签名证书路径
        $private_key = $this->getPrivateKey ( $cert_path, $cert_pwd);
        // 签名
        $sign_falg = openssl_sign ( $params_sha1x16, $signature, $private_key, OPENSSL_ALGO_SHA1 );
        if ($sign_falg) {
            $signature_base64 = base64_encode ( $signature );
            $params ['signature'] = $signature_base64;
        } else {
              echo  "签名失败";
              exit;
        }
    }

    public function coverParamsToString($params) {
        $sign_str = '';
        // 排序
        ksort ( $params );
        foreach ( $params as $key => $val ) {
            if ($key == 'signature') {
                continue;
            }
            $sign_str .= sprintf ( "%s=%s&", $key, $val );
        }
        return substr ( $sign_str, 0, strlen ( $sign_str ) - 1 );
    }

    public function getPrivateKey($cert_path, $cert_pwd) {
        $pkcs12 = file_get_contents ( $cert_path );
        openssl_pkcs12_read ( $pkcs12, $certs, $cert_pwd );
        return $certs ['pkey'];
    }

    public function create_html($params, $submit_url, $target='')
    {
        $sHtml = "<form id='applyForm' target='".$target."' name='applyForm' method='".$this->submit_method."' action='" . $submit_url . "'>";
        foreach($params as $key => $val)
        {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        $sHtml = $sHtml . "<input type='submit' value='submitForm'></form>";
        $sHtml = $sHtml."<script>document.forms['applyForm'].submit();</script>";
        return $sHtml;
    }

    /**
     * 生成支付表单 - 自动提交
     * @params null
     * @return null
     */
    public function gen_form(){}

    /**
     * 校验方法
     * @param null
     * @return boolean
     */
    public function is_fields_valiad(){
        return true;
    }

    /**
     * 支付后返回后处理的事件的动作
     * @params array - 所有返回的参数，包括POST和GET
     * @return null
     */
    public function callback(&$recv)
    {
        $mer_id = $this->getConf('mer_id', __CLASS__);
        if($this->is_return_vaild($recv) === 1){
            if($recv['respMsg']=='success' || $recv['respMsg'] == 'Success!' ){
                $ret['payment_id'] = $recv['orderId'];
                $ret['account'] = $mer_id;
                $ret['currency'] = 'CNY';
                $ret['trade_no'] = $recv['queryId'];
                $ret['money'] = number_format(($recv['txnAmt']/100),2,".","");
                $ret['paycost'] = '0.000';
                $ret['cur_money'] = $ret['money'];
                $ret['t_payed'] = strtotime($recv['notify_time']) ? strtotime($recv['notify_time']) : time();
                $ret['pay_app_id'] = "upacp";
                $ret['pay_type'] = 'online';
                $ret['status'] = 'succ';
            }
            else
            {
                $message = 'fail';
                $ret['status'] = 'invalid';
            }
        }
        else
        {
            $message = 'Invalid Sign';
            $ret['status'] = 'invalid';
        }

        return $ret;
    }

    public function is_return_vaild($params) {
        // 公钥
        $public_key = $this->getPulbicKeyByCertId ( $params ['certId'] );
        if(empty($public_key))
        {
            echo 'key_error';
            return null;
        }

        // 签名串
        $signature_str = $params ['signature'];
        unset ( $params ['signature'] );
        $params_str = $this->coverParamsToString ( $params );
        $signature = base64_decode ( $signature_str );
        $params_sha1x16 = sha1 ( $params_str, FALSE );
        $isSuccess = openssl_verify ( $params_sha1x16, $signature,$public_key, OPENSSL_ALGO_SHA1 );
        return $isSuccess;
    }

    public function getPulbicKeyByCertId($certId) {
        // 证书目录
        $certdir = ROOT_DIR . "/app/ectools/lib/payment/plugin/upacp";
        $handle = opendir($certdir);
        if ($handle) {
            while ( $file = readdir($handle)) {
                clearstatcache();
                $filePath = $certdir .'/ '.$file;
                $filePath = str_replace(' ', '', $filePath);
                if (is_file ($filePath)) {
                    if (pathinfo($file,PATHINFO_EXTENSION) == 'cer') {
                        if ($this->getCertIdByCerPath($filePath) == $certId){
                            closedir($handle);
                            return file_get_contents ($filePath);//取证书公钥 -验签
                        }
                    }
                }
            }
        }
        closedir($handle);
        return null;
    }
    /**
     * 取证书ID(.cer)
     *
     * @param unknown_type $cert_path
     */
    public function getCertIdByCerPath($cert_path) {
        $x509data = file_get_contents ( $cert_path );
        openssl_x509_read ( $x509data );
        $certdata = openssl_x509_parse ( $x509data );
        $cert_id = $certdata ['serialNumber'];
        return $cert_id;
    }

/* 以下为退款代码 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ */

    /**
     * 提交退款支付信息的接口
     * @param array 提交信息的数组
     * @return mixed false or null
     */
    public function dorefund($payment)
    {
        $mer_id = $this->getConf('mer_id', __CLASS__);
        $cert_dir = DATA_DIR . '/cert/payment_plugin_upacp/';
        $sdk_sign_cert_path = $cert_dir.trim($this->getConf('sdk_sign_cert_path', __CLASS__));
        $sdk_sign_cert_pwd  = $this->getConf('sign_cert_pwd', __CLASS__);

        $params = array(
            //以下信息非特殊情况不需要改动
            'version' => '5.0.0',             //版本号
            'encoding' => 'utf-8',            //编码方式
            'certId' => $this->getCertId($sdk_sign_cert_path, $sdk_sign_cert_pwd),           //证书ID
            'signMethod' => '01',             //签名方法
            'txnType' => '04',                //交易类型
            'txnSubType' => '00',             //交易子类
            'bizType' => '000201',            //业务类型
            'accessType' => '0',              //接入类型
            'channelType' => '07',            //渠道类型
            'backUrl' => $this->notify_url_refund, //后台通知地址
            //TODO 以下信息需要填写
            'orderId'     => $payment['refund_id'],     //商户订单号，8-32位数字字母，不能含“-”或“_”，可以自行定制规则，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
            'merId'       => $mer_id,         //商户代码，请改成自己的测试商户号，此处默认取demo演示页面传递的参数
            'origQryId'   => $payment['trade_no'], //原消费的queryId，可以从查询接口或者通知接口中获取，此处默认取demo演示页面传递的参数
            'txnTime'     => date('YmdHis'),     //订单发送时间，格式为YYYYMMDDhhmmss，重新产生，不同于原消费，此处默认取demo演示页面传递的参数
            'txnAmt'      => number_format($payment['refund_fee'],2,".","")*100,       //交易金额，退货总金额需要小于等于原消费
            // 'reqReserved' =>'透传信息',            //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
        );

        $this->sign($params, $sdk_sign_cert_path, $sdk_sign_cert_pwd);

        $response = client::post($this->sdk_back_trans_url, ['verify'=>false, 'body' => $params])->getBody();
        $result = $this->convertStringToArray($response);
        logger::info('中国银联退(pc)款返回信息格式化：'.var_export($result, 1));

        $ret['refund_id'] = $payment['refund_id'];
        $ret['trade_no'] = $payment['trade_no'];
        $ret['refund_fee'] = number_format(($result['txnAmt']/100),2,".","");
        // $ret['third_transaction_id'] = $payment['queryId'];//退款的第三方退款交易号

        /*
        if ($result["respCode"] == "03" || $result["respCode"] == "04" || $result["respCode"] == "05" ){
            //后续需发起交易状态查询交易确定交易状态
             echo "处理超时，请稍微查询。<br>\n";
        } else {
            //其他应答码做以失败处理
             echo "失败：" . $result["respMsg"] . "。<br>\n";
        }
        */

        if($result['respCode'] === '00')
        {
            $ret['status'] = 'succ';
        }
        else
        {
            $ret['status'] = 'failed';
        }
        return $ret;
    }

    /**
     * 处理第三方支付方式退款返回的信息
     * @param array 第三方支付方式返回的信息
     * @return mixed
     */
    public function refundcallback(&$recv)
    {
        if($this->is_return_vaild($recv) === 1){
            if($recv['respCode']==='00'){
                // $ret['third_transaction_id'] = $recv['queryId'];//退款的第三方退款交易号
                $ret['refund_id'] = $recv['orderId'];//退款时的退款单号，refunds表中的refund_id
                $ret['trade_no'] = $recv['origQryId'];//原第三方支付单号，支付单表中的trade_no
                $ret['refund_fee'] = number_format(($recv['settleAmt']/100),2,".","");
                $ret['status'] = 'REFUND_SUCCESS';
            }
        }

        return $ret;
    }

    /**
     * key1=value1&key2=value2转array
     * @param $str key1=value1&key2=value2的字符串
     */
    public function convertStringToArray($str)
    {
        $arrStr = [];
        $arrSplits = [];
        $arrQueryStrs = [];

        if ($str)
        {
            $arrStr = explode("&", $str);
            foreach ($arrStr as $str)
            {
                $arrSplits = explode("=", $str);
                $arrQueryStrs[urldecode($arrSplits[0])] = urldecode($arrSplits[1]);
            }
        }
        return $arrQueryStrs;
    }

/* 以上为退款代码 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ */

}
