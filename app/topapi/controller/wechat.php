<?php
class topapi_ctl_wechat{

    public function wxpayApp()
    {
        // 新微信支付回调地址
        $postData = array();
        $httpclient = kernel::single('base_httpclient');
        $callback_url = kernel::openapi_url('openapi.ectools_payment/parse/ectools_payment_plugin_wxpayApp', 'callback');

        $postStr = file_get_contents("php://input");//$GLOBALS["HTTP_RAW_POST_DATA"];
        logger::info('wxpayApp data, xml to array :'.var_export($postStr, 1));
        $postArray = kernel::single('site_utility_xml')->xml2array($postStr);
        $postData['weixin_postdata']  = $postArray['xml'];
        $nodify_data = array_merge(input::get(), $postData);
        $response = $httpclient->post($callback_url, $nodify_data);
        // if($notify->checkSign() == FALSE){
        //     $arr = array('return_code'=>'FAIL','return_msg'=>'签名失败')；
        // }else{
        //     $arr = array('return_code'=>'SUCCESS');
        // }
        // $returnXml = $notify->returnXml();
        // echo $returnXml;exit;

    }
}
