<?php
/**
 * topapi
 *
 * -- member.aftersales.apply
 * -- 会员申请退换货
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_member_aftersales_apply implements topapi_interface_api {

    /**
     * api接口的名称
     * @var string
     */
    public $apiDescription = '会员申请退换货';

    public function setParams()
    {
        //接口传入的参数
        $return = array(
            'tid'               => ['type'=>'int',  'valid'=>'required|numeric',   'desc'=>'申请售后的订单编号'],
            'oid'               => ['type'=>'int',  'valid'=>'required|numeric',   'desc'=>'申请售后的子订单编号'],
            'reason'            => ['type'=>'string', 'valid'=>'required', 'desc'=>'售后原因,如果是“其他原因”则描述必填', 'msg'=>'请选择售后原因'],
            'description'       => ['type'=>'string', 'valid'=>'required_if:reason,其他原因|max:300', 'desc'=>'售后详细描述', 'msg'=>'请填写问题描述|描述最多填写300个字'],
            'aftersales_type'   => ['type'=>'string', 'valid'=>'required', 'desc'=>'售后类型', 'msg'=>'请选择售后类型'],
            'evidence_pic'      => ['type'=>'string', 'valid'=>'', 'desc'=>'照片凭证,imageId逗号隔开,最多五张照片'],
        );

        return $return;
    }

    public function handle($params)
    {
        $result = app::get('topapi')->rpcCall('aftersales.apply', $params);
        return $result;
    }
}
