<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


/**
 * 外部支付接口统一调用的api类
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_api
{
    /**
     * @var object 应用对象的实例。
     */
    private $app;

    /**
     * 构造方法
     * @param object 当前应用的app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 支付返回后的同意支付处理
     * @params array - 页面参数
     * @return null
     */
    public function parse($params='')
    {
        // 取到内部系统参数
        $arr_pathInfo = explode('?', $_SERVER['REQUEST_URI']);
        $pathInfo = substr($arr_pathInfo[0], strpos($arr_pathInfo[0], "parse/") + 6);
        $objShopApp = $this->getAppName($pathInfo);
        $innerArgs = explode('/', $pathInfo);
        $class_name = array_shift($innerArgs);
        $method = array_shift($innerArgs);

        $arrStr = array();
        $arrSplits = array();
        $arrQueryStrs = array();
        // QUERY_STRING
        if (isset($arr_pathInfo[1]) && $arr_pathInfo[1])
        {
            $querystring = $arr_pathInfo[1];
        }
        if ($querystring)
        {
            $arrStr = explode("&", $querystring);

            foreach ($arrStr as $str)
            {
                $arrSplits = explode("=", $str);
                $arrQueryStrs[urldecode($arrSplits[0])] = urldecode($arrSplits[1]);
            }
        }
        else
        {
            if ($_POST)
            {
                $arrQueryStrs = $_POST;
            }
        }

        if( get_parent_class($class_name ) != 'ectools_payment_app')
            throw new LogicException('Plugin Error!');
        logger::info("支付返回信息记录：".var_export($arrQueryStrs,1));
        $payments = new $class_name($objShopApp);
        $ret = $payments->$method($arrQueryStrs);
        logger::info("支付、退款返回信息转换之后记录：".var_export($ret,1));
        // 支付结束，回调服务.
        if (!isset($ret['status']) || $ret['status'] == '') $ret['status'] = 'failed';

        if($ret['payment_id'])
        {
            $objPayments = app::get('ectools')->model('payments');
            $sdf = $objPayments->getRow('*',array('payment_id'=>$ret['payment_id']));
            if ($sdf)
            {
                $sdf['account'] = $ret['account'];
                $sdf['bank'] = $ret['bank'];
                $sdf['pay_account'] = $ret['pay_account'];
                $sdf['currency'] = $ret['currency'];
                $sdf['trade_no'] = $ret['trade_no'];
                $sdf['payed_time'] = $ret['t_payed'];
                $sdf['pay_app_id'] = $ret['pay_app_id'];
                $ret['pay_type'] = $sdf['pay_type'];
                $sdf['memo'] = $ret['memo'];
                $sdf['money'] = $ret['money'];
                $sdf['cur_money'] = $ret['cur_money'] ? $ret['cur_money'] : $sdf['cur_money'];
            }
        }

        switch ($ret['status'])
        {
            case 'succ':
            case 'progress':
                if ($sdf && $sdf['status'] != 'succ')
                {
                    $isUpdatedPay = payment::update($ret, $msg);
                    if($isUpdatedPay)
                    {
                        $params['payment_id'] = $sdf['payment_id'];
                        $params['fields'] = 'status,payment_id,pay_type,user_id,cur_money';
                        try
                        {
                            $paymentBill = app::get('ectools')->rpcCall('payment.bill.get',$params);
                        }
                        catch(Exception $e)
                        {
                            throw $e;
                        }

                        try
                        {
                            logger::info("支付过程中，被处理的订单数据： \n".var_export($paymentBill,1));
                            if($paymentBill['status'] == "succ" || $paymentBill['status'] == "progress")
                            {
                                foreach($paymentBill['trade'] as $value)
                                {
                                    app::get('ectools')->rpcCall('trade.pay.finish',array('tid'=>$value['tid'],'payment'=>$value['payment']));
                                }
                            }
                        }
                        catch(\Exception $e)
                        {
                            logger::info("支付过程中，处理订单出错后：$e->getMessage() \n".var_export($paymentBill,1)."\n----end----\n");
                            throw $e;
                        }
                    }

                    //支付成功给支付网关显示支付信息
                    if(method_exists($payments, 'ret_result')){
                        if($ret['pay_app_id'] == "teegonali"  ||  $ret['pay_app_id'] == "teegonwxpay"){
                            $payments->ret_result($ret['money']);
                        }else{
                            $payments->ret_result($ret['payment_id']);
                        }
                    }
                }
                break;
            case 'REFUND_SUCCESS':
                // 退款成功操作
                if ($ret)
                {
                    // 如果已经更新过，对应的api会做判断，防止多次执行
                    //更新退款单状态
                    $apiParams = ['refund_id'=>$ret['refund_id'], 'status'=>'succ'];
                    $result = app::get('ectools')->rpcCall('refund.update', $apiParams);
                    //更改退款申请单
                    $refunds = app::get('ectools')->model('refunds')->getList('return_fee,refunds_id', ['refund_id'=>$ret['refund_id']]);
                    $apiParams = ['return_fee'=>$refunds[0]['return_fee'], 'refunds_id'=>$refunds[0]['refunds_id']];
                    app::get('sysaftersales')->rpcCall('aftersales.refunds.restore', $apiParams);
                    //退款成功给支付网关显示支付信息
                    if(method_exists($payments, 'refund_result'))
                    {
                        $payments->ret_result($ret['refund_id']);
                    }
                }
                break;
            case 'PAY_PDT_SUCC':
                $ret['status'] = 'succ';
                // 无需更新状态.
                break;
            case 'failed':
            case 'error':
            case 'cancel':
            case 'invalid':
            case 'timeout':
                $is_updated = false;
                $isUpdatedPay = payment::update($ret, $msg);
                break;
        }

        // Redirect page.
        if ($sdf['return_url'])
        {
            //header('Location: '.kernel::removeIndex(request::root()).$sdf['return_url']);
            $sdf['return_url'] = unserialize($sdf['return_url']);
            header('Location: '.url::action($sdf['return_url'][0],$sdf['return_url'][1]));
        }
    }

    /**
     * 得到实例应用名
     * @params string - 请求的url
     * @return object - 应用实例
     */
    private function getAppName($strUrl='')
    {
        //todo.
        if (strpos($strUrl, '/') !== false)
        {
            $arrUrl = explode('/', $strUrl);
        }
        return app::get($arrUrl[0]);
    }
}
