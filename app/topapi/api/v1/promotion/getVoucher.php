<?php
class topapi_api_v1_promotion_getVoucher implements topapi_interface_api{

	public $apiDescription = '获取购物券详情';
	public $use_strict_filter = true;

    public function setParams()
    {
        return [
            'voucher_id'   => ['type'=>'int', 'valid'=>'required|min:1', 'desc'=>'购物券id', 'msg'=>'']
        ];
        return $return;
    }

    public function handle($params)
    {
		$params['fields'] = 'voucher_id,voucher_name,promotion_tag,used_platform,limit_cat,limit_money,deduct_money,canuse_start_time,canuse_end_time,valid_status';
		$voucher = app::get('topwap')->rpcCall('promotion.voucher.get',$params);

        
        $this->__platform($voucher);
        $pagedata['voucher'] = $voucher;
        return $pagedata;

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
}

