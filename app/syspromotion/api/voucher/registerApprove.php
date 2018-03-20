<?php
/**
 * 购物券报名审核
 *
 * promotion.voucher.register.approve
 */
class syspromotion_api_voucher_registerApprove {

    public $apiDescription = "购物券报名审核";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $data['params'] = array(
            'voucher_id' => ['type'=>'int', 'valid'=>'required|integer', 'desc'=>'购物券id'],
            'shop_id' => ['type'=>'int', 'valid'=>'required|integer', 'desc'=>'店铺id'],
            'status' => ['type'=>'string', 'valid'=>'required|in:agree,refuse', 'desc'=>'审核状态'],
            'reason' => ['type'=>'string', 'valid'=>'required_if:status,refuse', 'desc'=>'驳回原因'],
        );
        return $data;
    }

    public function handle($params)
    {
        $voucherInfo = app::get('syspromotion')->model('voucher')->getRow('*', ['voucher_id'=>$params['voucher_id']]);

        $objMdlVoucherRegister = app::get('syspromotion')->model('voucher_register');
        $voucherRegisterInfo = $objMdlVoucherRegister->getRow('id,verify_status,verify_status', ['voucher_id'=>$params['voucher_id'],'shop_id'=>$params['shop_id']]);

        if( !$voucherRegisterInfo || !$voucherInfo )
        {
            throw new LogicException('审核数据不存在');
        }

        if( !$voucherInfo['valid_status'] )
        {
            throw new LogicException('购物券已失效，无需审核');
        }

        $updateData['verify_status'] = $params['status'];
        if( $params['status'] == 'refuse' )
        {
            $updateData['refuse_reason'] = $params['reason'];
        }
        $updateData['modified_time'] = time();

        return $objMdlVoucherRegister->update($updateData, ['voucher_id'=>$params['voucher_id'],'shop_id'=>$params['shop_id']]);
    }
}
