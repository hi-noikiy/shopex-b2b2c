<?php
/**
 * topapi
 *
 * -- member.coupon.list
 * -- 会员我的购物券列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_voucher_list implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员我的购物券列表';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'page_no'   => ['type'=>'int',    'valid'=>'',  'example'=>'', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',    'valid'=>'',  'example'=>'', 'desc'=>'每页数据条数,默认20条'],
            'fields'    => ['type'=>'field',  'valid'=>'',  'example'=>'', 'desc'=>'需要的字段'],
            'orderBy'   => ['type'=>'string', 'valid'=>'',  'example'=>'', 'desc'=>'排序'],
            'is_valid'  => ['type'=>'int',    'valid'=>'in:0,1,2', 'example'=>'', 'desc'=>'获取是否有效的参数 0已使用 1有效 2已过期'],
            'platform'  => ['type'=>'string', 'valid'=>'in:pc,wap,app', 'example'=>'', 'desc'=>'购物券使用平台 pc 电脑端 wap 手机端 app端 如果是全部则不需要传入参数'],
        );

        return $return;
    }

    /**
     * @return string voucher_code 购物券号码
     * @return int user_id 会员ID
     * @return int voucher_id 会员购物券ID
     * @return string obtain_desc 领取方式
     * @return timestamp obtain_time 购物券获得时间
     * @return int tid 订单ID
     * @return string is_valid 会员购物券是否当前可用(0:已使用；1:有效；2:过期)
     * @return string used_platform 使用平台(0:；1:；2: 3)
     * @return timestamp canuse_start_time 生效时间
     * @return timestamp canuse_end_time 失效时间
     * @return number limit_money 满足条件金额
     * @return number deduct_money 优惠金额
     * @return string voucher_name 购物券名称
     * @return string coupon_desc 购物券描述
     * @return string limit_cat 支持商品类目
     * @return string subsidy_proportion 平台补贴比例
     */
    public function handle($params)
    {
        $voucherListData = app::get('topapi')->rpcCall('user.voucher.list.get', $params);

        $return = array();
        if( $voucherListData['list'] )
        {
            foreach($voucherListData['list'] as $k=>$voucherInfo){
                $this->__platform($voucherInfo);
                $return['list'][$k] = $voucherInfo;
            }

            $return['pagers']['total'] = $voucherListData['pagers']['total'];
            $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
            $return['cur_symbol'] = $cur_symbol;
            $return['now'] = time();
        }

        return $return;
    }

    private function __platform(&$data)
    {
        $platform = $data['used_platform'];
        $platArr = array(
            'pc' =>'pc端',
            'wap' =>'H5端',
            'app' =>'APP端',
        );
        $data['available'] = 0;
        foreach(explode(',',$platform) as $value)
        {
            $result[] = $platArr[$value];
            if($value == "wap")
            {
                $data['available'] = 1;
            }
        }
        $data['used_platform'] = implode(' ',$result);
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"coupon_code":"BP62U003DC00003","user_id":4,"coupon_id":12,"obtain_desc":"免费领取","obtain_time":1470293483,"tid":null,"is_valid":"1","used_platform":"0","price":"20.000","start_time":1453824000,"end_time":1609257600,"canuse_start_time":1453824000,"canuse_end_time":1609257600,"limit_money":"500.000","deduct_money":"20.000","coupon_name":"智能设备类 满500减20","coupon_desc":"智能设备类 满500减20","shop_name":"onexbbc自营店（自营店铺）自营店"}],"pagers":{"total":4},"cur_symbol":{"sign":"￥","decimals":2}}}';
    }
}
