<?php
/**
 * promotion.voucher.register
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 商家对指定购物券进行报名
 */
final class syspromotion_api_voucher_register {

    public $apiDescription = "商家对指定购物券进行报名";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $data['params'] = array(
            'voucher_id' => ['type'=>'int', 'valid'=>'required|integer', 'desc'=>'购物券ID'],
            'shop_id'    => ['type'=>'int', 'valid'=>'required|integer', 'desc'=>'店铺id'],
            'cat_id'     => ['type'=>'string','valid'=>'required','desc'=>'参加的类目ID，多个用","隔开','msg'=>'请选择参加类目']
        );
        return $data;
    }

    public function handle($params)
    {
        $voucherInfo = app::get('syspromotion')->model('voucher')->getRow('*', ['voucher_id'=>$params['voucher_id']]);

        //判断是否已报名
        $objMdlVoucherRegister = app::get('syspromotion')->model('voucher_register');
        $voucherRegisterInfo = $objMdlVoucherRegister->getRow('id,verify_status', ['voucher_id'=>$params['voucher_id'],'shop_id'=>$params['shop_id']]);

        $this->__check($params, $voucherInfo, $voucherRegisterInfo);

        $data['shop_id']       = $params['shop_id'];
        $data['voucher_id']    = $voucherInfo['voucher_id'];
        $data['cat_id']        = $params['cat_id'];
        $data['modified_time'] = time();
        $data['created_time']  = time();

        return $objMdlVoucherRegister->insert($data);
    }

    private function __check($params, $voucherInfo, $voucherRegisterInfo)
    {
        if( $voucherRegisterInfo )
        {
            throw new LogicException('不可重复报名');
        }

        if( !$voucherInfo['valid_status'] )
        {
            throw new LogicException('该购物券已失效');
        }

        $shopParams = array(
            'shop_id' => $params['shop_id'],
            'fields' =>'cat.cat_name,cat.cat_id',
        );
        $shopdata = app::get('topshop')->rpcCall('shop.get.detail',$shopParams);
        foreach ($shopdata['cat'] as $key => $value)
        {
            $shopCatIds[$key] = $value['cat_id'];
        }
        $voucherInfo['limit_cat'] = explode(',',$voucherInfo['limit_cat']);
        $shopLimitCat = array_intersect($shopCatIds,$voucherInfo['limit_cat']);

        $catId = explode(',',$params['cat_id']);
        $shoptype = $shopdata['shop']['shop_type'];
        if( !$shopLimitCat || !in_array($shoptype,explode(',',$voucherInfo['shoptype'])))
        {
            //判断报名店铺类型是否有资格
            //判断报名店铺类目是否支持
            throw new LogicException('当前店铺不具备报名资格');
        }

        if($catId != array_intersect($catId, $shopLimitCat)  )
        {
            throw new LogicException('请选择指定参加的的类目');
        }

        //判断报名时间
        if( $voucherInfo && $voucherInfo['apply_begin_time'] > time() )
        {
            throw new LogicException('还未到报名开始时间，请等到报名时间！');
        }

        if( $voucherInfo['apply_end_time'] < time() )
        {
            throw new LogicException('报名时间已截止！');
        }

        return true;
    }
}
