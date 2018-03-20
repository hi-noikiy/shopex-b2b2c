<?php
/**
 * promotion.voucher.update
 *
 * ShopEx licence *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 添加购物券
 */
final class syspromotion_api_voucher_update {

    public $apiDescription = '更新购物券基本信息';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id'    => ['type'=>'int',    'valid'=>'required', 'desc'=>'购物券ID'],
            'voucher_name'  => ['type'=>'string', 'valid'=>'required', 'desc'=>'购物券券名称', 'msg'=>'请填写购物券名称'],
            'voucher_desc'  => ['type'=>'string', 'valid'=>'max:50', 'desc'=>'购物券描述', 'msg'=>'购物券描述不能超过50个字'],

            'limit_money'   => ['type'=>'string', 'valid'=>'required|numeric|than:deduct_money',  'desc'=>'满足条件金额', 'example'=>'100', 'msg'=>'请填写购物券满足金额条件|请填写正确的购物券满足金额|购物券满减金额需大于减去金额'],
            'deduct_money'  => ['type'=>'string', 'valid'=>'required|numeric',  'desc'=>'购物金额', 'example'=>'10', 'msg'=>'请填写购物券购物金额|请填写正确的购物券金额'],
            'subsidy_proportion' => ['type'=>'string', 'valid'=>'required|numeric|between:0,100', 'desc'=>'平台补贴百分比', 'example'=>'10', 'msg'=>'请填写平台补贴百分比|请填写正确的平台补贴百分比|请填写正确的平台补贴百分比'],
            'max_gen_quantity' => ['type'=>'string',   'valid'=>'required|integer|min:1', 'desc'=>'生成购物券总数量', 'example'=>'10', 'msg'=>'请填写购物券生成总数量|请填写购物券生成总数量|购物券生成总数量最少大于1'],
            'used_platform'  => ['type'=>'string', 'valid'=>'required', 'desc'=>'使用平台 pc,wap,app', 'example'=>'pc', 'msg'=>'请选择使用平台'],

            'apply_begin_time' => ['type'=>'string', 'valid'=>'required', 'example'=>'1483150679', 'desc'=>'购物券商家报名开始时间', 'msg'=>'请设置商家报名开始时间'],
            'apply_end_time'   => ['type'=>'string', 'valid'=>'required|than:apply_begin_time', 'example'=>'1483150679', 'desc'=>'购物券商家报名结束时间', 'msg'=>'请设置商家报名结束时间|商家报名结束时间需大于报名开启时间'],
            'shoptype'        => ['type'=>'string', 'valid'=>'required', 'example'=>'1483150679', 'desc'=>'可报名店铺类型', 'msg'=>'请选择可报名店铺类型'],
            'limit_cat'        => ['type'=>'string', 'valid'=>'required', 'example'=>'1,2,3', 'desc'=>'可参加报名的商品类目,逗号隔开类目ID', 'msg'=>'请选择参加购物券的类目'],

            'userlimit_quantity'=> ['type'=>'int', 'valid'=>'required|integer|sthan:max_gen_quantity', 'desc'=>'用户总计可领取数量', 'example'=>'10', 'msg'=>'请填写购物券用户可领取总数量|请填写正确的数量|用户可领取数量应小于等于总数量'],
            'cansend_start_time'=> ['type'=>'string', 'valid'=>'required|than:apply_end_time', 'example'=>'1483150679', 'desc'=>'购物券可领取开始时间', 'msg'=>'请选择购物券可领取开始时间|购物券领取时间需大于报名结束时间'],
            'cansend_end_time'  => ['type'=>'string', 'valid'=>'required|than:cansend_start_time', 'example'=>'1483150679', 'desc'=>'购物券可领取结束时间', 'msg'=>'请选择购物券可领取结束时间|购物券可领取时间需大于领取开始时间'],
            'canuse_start_time' => ['type'=>'string', 'valid'=>'required|bthan:cansend_start_time', 'example'=>'1483150679', 'desc'=>'购物券生效开始时间', 'msg'=>'请选择购物券生效开始时间|购物券生效时间需大于等于领取开始时间'],
            'canuse_end_time'   => ['type'=>'string', 'valid'=>'required|than:canuse_start_time', 'example'=>'1483150679', 'desc'=>'购物券生效结束时间', 'msg'=>'请选择购物券生效结束时间|购物券生效结束时间需大于生效开始时间'],
            'valid_grade'       => ['type'=>'string', 'valid'=>'required', 'example'=>'1,2,3,4,5',  'desc'=>'适用会员,会员等级ID', 'msg'=>'请选择使用的会员等级'],
        );

        return $return;
    }

    /**
     * @return
     */
    public function update($apiData)
    {
        $apiData['voucher_name'] = strip_tags($apiData['voucher_name']);
        $apiData['voucher_desc'] = strip_tags($apiData['voucher_desc']);
        $apiData['promotion_tag'] = '购物券';

        $objMdlVoucher = app::get('syspromotion')->model('voucher');
        $info = $objMdlVoucher->getRow('voucher_id,apply_begin_time',['voucher_name'=>$apiData['voucher_name']]);

        if( $info['apply_begin_time'] <= time() )
        {
            throw new LogicException('商家已经开始报名，不可在修改');
        }

        if( $info['voucher_id'] != $apiData['voucher_id'] )
        {
            throw new LogicException('购物券名称重复');
        }

        $objMdlVoucher->save($apiData);

        return kernel::single('syspromotion_data_promotion_voucher')->initVoucherToRedis($apiData['voucher_id'], $apiData['max_gen_quantity']);
    }
}

