<?php

class topapi_api_v1_cart_getVoucher implements topapi_interface_api{
	/**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员可使用的购物券';

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'catItemPrice'   => ['type'=>'json',    'valid'=>'required',  'example'=>'', 'desc'=>'购物车商品金额'],
            'voucher_code' => ['type'=>'int',    'valid'=>'',  'example'=>'', 'desc'=>'当前选中的购物券'],
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
        $return['list'] = [];
        $catItemPrice = json_decode($params['catItemPrice'],1);
        $checked = $params['voucher_code'];
        $filter['pages'] = 1;
        $pageSize = 100;
        $params = array(
            'page_no' => $filter['pages'],
            'page_size' => $pageSize,
            'fields' => '*',
            'user_id' => $params['user_id'],
            'is_valid' => 1,
            'platform' => 'app',
        );
        $voucherListData = app::get('topwap')->rpcCall('user.voucher.list.get', $params);
        if(!$voucherListData['list']) return $return;
        foreach( $voucherListData['list'] as $k=>$voucherInfo)
        {
            //判断使用条件
            $limitCat = explode(',', $voucherInfo['limit_cat']);//限制类目
            $limitMoney = $voucherInfo['limit_money'];//限制金额
            $deductMoney = 0;
            $voucherTotalPrice = 0;
            foreach( $catItemPrice as $lv1CatId=>$row )
            {
                if( !in_array($lv1CatId, $limitCat)  )
                {
                    continue;
                }

                foreach( $row as $shopId => $shopPriceTotal )
                {
                    $params = [
                        'shop_id'   =>$shopId,
                        'voucher_id'=>$voucherInfo['voucher_id'],
                        'fields'    => 'verify_status,valid_status,cat_id',
                    ];
                    $voucherRegisterInfo = app::get('topwap')->rpcCall('promotion.voucher.register.get', $params);
                    if( !$voucherRegisterInfo || $voucherRegisterInfo['verify_status'] != 'agree' || $voucherRegisterInfo['valid_status'] !=1 )
                    {
                        continue;
                    }

                    $shopCatId = explode(',', $voucherRegisterInfo['cat_id']);
                    if( in_array($lv1CatId, $shopCatId)  )
                    {
                        //使用购物券的商品金额
                        $voucherTotalPrice = ecmath::number_plus(array($voucherTotalPrice, $shopPriceTotal['price']));
                    }
                }
            }
            $voucherInfo['start_time'] = date('Y-m-d',$voucherInfo['start_time']);
            $voucherInfo['end_time'] = date('Y-m-d',$voucherInfo['end_time']);
            $this->__platform($voucherInfo);
            if($checked && $checked == $voucherInfo['voucher_code'])
            {
                $voucherInfo['checked'] = 1;
            }

            if( $voucherTotalPrice >= $limitMoney  )
            {
                //购物券金额
                $deductMoney = $voucherInfo['deduct_money'];
                $return['list'][] = $voucherInfo;
            }
        }
        if($return['list'])
        {
            $cur_symbol = app::get('topapi')->rpcCall('currency.get.symbol',array());
            $return['cur_symbol'] = $cur_symbol;
        }
        return $return;
    }

    private function __platform(&$data)
    {
        $platform = $data['used_platform'];
        $platArr = array(
            'pc' =>'电脑端',
            'wap' =>'触屏端',
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
        $data['used_platform'] = implode(',',$result);
    }

    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"coupon_code":"BP62U003DC00003","user_id":4,"coupon_id":12,"obtain_desc":"免费领取","obtain_time":1470293483,"tid":null,"is_valid":"1","used_platform":"0","price":"20.000","start_time":1453824000,"end_time":1609257600,"canuse_start_time":1453824000,"canuse_end_time":1609257600,"limit_money":"500.000","deduct_money":"20.000","coupon_name":"智能设备类 满500减20","coupon_desc":"智能设备类 满500减20","shop_name":"onexbbc自营店（自营店铺）自营店"}],"pagers":{"total":4},"cur_symbol":{"sign":"￥","decimals":2}}}';
    }
}
