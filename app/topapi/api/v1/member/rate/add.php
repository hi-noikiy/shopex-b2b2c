<?php
/**
 * ShopEx licence
 *
 ** -- member.rate.add
 * -- 对已完成的订单新增商品评论和店铺评分
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 */
class topapi_api_v1_member_rate_add implements topapi_interface_api {

    /**
     * 接口作用说明
     */
    public $apiDescription = '对已完成的订单新增商品评论和店铺评分';

    public function setParams()
    {
        return array(
            'tid' => ['type'=>'string', 'valid'=>'required', 'example'=>'1608151930150004', 'desc'=>'新增评论的订单ID'],

            'rate_data' => ['type'=>'jsonArray', 'valid'=>'required', 'example'=>'', 'desc'=>'对子订单评论的参数', 'msg'=>'请填写商品评价' ,'params' => [
                //单个子订单评论需要的参数
                'oid'      => ['type'=>'string',  'valid'=>'required', 'example'=>'', 'desc'=>'新增评论的子订单号'],
                'result'   => ['type'=>'string',  'valid'=>'required|in:good,neutral,bad', 'example'=>'', 'desc'=>'评价结果,good 好评 neutral 中评 bad 差评'],
                'content'  => ['type'=>'string',  'valid'=>'required_if:result,neutral,bad|max:300', 'example'=>'', 'desc'=>'评价内容', 'msg'=>'中评或差评，请填写评价内容'],
                'rate_pic' => ['type'=>'string',  'valid'=>'', 'example'=>'', 'desc'=>'晒单图片，多个图片用逗号隔开'],
            ]],
           'anony'    => ['type'=>'string','valid'=>'required', 'example'=>'true', 'desc'=>'是否匿名，true匿名 false不匿名'],

            //店铺动态评分参数
            'tally_score'               => ['type'=>'int','valid'=>'numeric|required|between:1,5', 'example'=>'5', 'desc'=>'商品与描述相符', 'msg'=>'请评价店铺商品与描述|请评价店铺商品与描述|请评价店铺商品与描述'],
            'attitude_score'            => ['type'=>'int','valid'=>'numeric|required|between:1,5', 'example'=>'5', 'desc'=>'服务态度评分', 'msg'=>'请评价店铺商品与描述|请评价店铺服务态度|请评价店铺服务态度'],
            'delivery_speed_score'      => ['type'=>'int','valid'=>'numeric|required|between:1,5', 'example'=>'5', 'desc'=>'发货速度评分', 'msg'=>'请评价店铺商品与描述|请评价店铺发货速度|请评价店铺发货速度'],
        );

        return $return;
    }

    public function handle($params)
    {
        $params['logistics_service_score'] = 5;

        foreach( $params['rate_data'] as &$row )
        {
            $row['anony'] = ($params['anony'] == 'true') ? 1 : 0;
        }
        unset($params['anony']);

        $params['rate_data'] = json_encode($params['rate_data']);
        $result = app::get('topapi')->rpcCall('rate.add', $params);
        return $result;
    }
}


