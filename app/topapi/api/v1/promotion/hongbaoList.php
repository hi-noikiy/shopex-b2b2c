<?php
/**
 * topapi
 *
 * -- promotion.activity.list
 * -- 获取平台活动列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_promotion_hongbaoList implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取会员的红包列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'status'   => ['type'=>'string','valid'=>'required|in:active,used,expired', 'example'=>'active', 'desc'=>'active:活动中的，comming:即将开始的活动', 'msg'=>'值必须是active或者comming'],
            //分页参数
            'page_no'   => ['type'=>'int','valid'=>'min:1|numeric', 'example'=>'1', 'desc'=>'分页当前页数,默认为1', 'msg'=>''],
            'page_size' => ['type'=>'int','valid'=>'', 'example'=>'10', 'desc'=>'每页数据条数,默认10条', 'msg'=>''],

        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $userId = $params['user_id'];
        $status = $params['status'];
        $pageNo = $params['page_no'] ? : 1;
        $pageSi = $params['page_size'] ? : 10;

        $apiParams = [
            'user_id' => $userId,
            'is_valid'=> $status,
            'page_no' => $pageNo,
            'page_size' => $pageSi,
            'fields'=>'*'
        ];

        $hongbaoData = app::get('topwap')->rpcCall(
            'user.hongbao.list.get', $apiParams);

        return $hongbaoData;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"id":1,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695853,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"2.000","start_time":1478016000,"end_time":1480435200},{"id":2,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695854,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"1.000","start_time":1478016000,"end_time":1480435200},{"id":3,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695855,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"2.000","start_time":1478016000,"end_time":1480435200},{"id":4,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695855,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"1.000","start_time":1478016000,"end_time":1480435200},{"id":5,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695856,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"2.000","start_time":1478016000,"end_time":1480435200},{"id":6,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695856,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"1.000","start_time":1478016000,"end_time":1480435200},{"id":7,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695856,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"2.000","start_time":1478016000,"end_time":1480435200},{"id":8,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695856,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"1.000","start_time":1478016000,"end_time":1480435200},{"id":9,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695856,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"2.000","start_time":1478016000,"end_time":1480435200},{"id":10,"name":"ttttttttttttttt`","hongbao_id":3,"user_id":4,"obtain_time":1479695857,"tid":null,"is_valid":"active","hongbao_obtain_type":"userGet","obtain_desc":"用户主动领取","refund_hongbao_tid":null,"used_platform":"all","hongbao_type":"fixed","money":"1.000","start_time":1478016000,"end_time":1480435200}],"pagers":{"total":14}}}';
    }

}
