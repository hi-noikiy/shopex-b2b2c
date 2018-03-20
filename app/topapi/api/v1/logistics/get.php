<?php

/**
 * topapi
 *
 * -- logistics.info
 * -- 物流详情
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_logistics_get implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '显示物流信息';

    public function setParams()
    {
        return  [
                'logi_no' => ['type' => 'string', 'valid' => 'required|min:6|max:20', 'desc' => '物流单号', 'msg'=>'请填写物流单号|请填写正确的物流单号|请填写正确的物流单号'],
                'corp_code' => ['type'=>'string', 'valid'=>'required', 'desc'=>'物流公司代码'],
        ];

    }

    public function handle($params)
    {
        $tracking = app::get('syslogistics')->getConf('syslogistics.order.tracking');
        $result['tracker'] = [];
        if($tracking == 'true' || is_null($tracking))
        {
            try
            {
                $result = app::get('topapi')->rpcCall('logistics.tracking.get.hqepay',$params);
                if( $result['tracker'] )
                {
                    $count = count($result['tracker'])-1;
                    $tracker = $result['tracker'];
                    unset($result['tracker']);
                    foreach( $tracker as $key=>$row )
                    {
                        $result['tracker'][$count] = $row;
                        $count--;
                    }
                    ksort($result['tracker']);
                }
                else
                {
                    $result['tracker'][] = array(
                        'AcceptTime' => date('Y-m-d H:i:s'),
                        'AcceptStation' => '暂无物流跟踪记录',
                    );
                }
            }
            catch( \LogicException $e )
            {
                $result['tracker'][] = array(
                    'AcceptTime' => date('Y-m-d H:i:s'),
                    'AcceptStation' => $e->getMessage(),
                );
            }
        }
        return $result;
    }
    
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"tracker":[{"AcceptTime":"2016-08-31 19:11:34","AcceptStation":"南宁市【南宁兴宁分部】，【美邦/0771-6719119】已揽收"},{"AcceptTime":"2016-08-31 19:22:00","AcceptStation":"南宁市【南宁兴宁分部】，正发往【南宁转运中心】"},{"AcceptTime":"2016-08-31 20:22:30","AcceptStation":"到南宁市【南宁转运中心】"},{"AcceptTime":"2016-09-01 03:36:33","AcceptStation":"南宁市【南宁转运中心】，正发往【上海转运中心】"},{"AcceptTime":"2016-09-02 20:10:59","AcceptStation":"到上海市【上海转运中心】"},{"AcceptTime":"2016-09-02 22:06:25","AcceptStation":"上海市【上海转运中心】，正发往【上海徐汇区桂林分部Q】"},{"AcceptTime":"2016-09-03 08:00:05","AcceptStation":"到上海市【上海徐汇区桂林分部Q】"},{"AcceptTime":"2016-09-03 08:21:32","AcceptStation":"上海市【上海徐汇区桂林分部Q】，【桂林站/64757435】正在派件"},{"AcceptTime":"2016-09-05 12:20:03","AcceptStation":"上海市【上海徐汇区桂林分部Q】，字迹潦草已签收"}]}}';
    }
}

