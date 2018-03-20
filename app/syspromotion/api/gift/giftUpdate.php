<?php
/**
 * 更新赠品促销
 */
class syspromotion_api_gift_giftUpdate {

	public $apiDescription = '修改赠品促销促销数据';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'        => ['type'=>'int',    'valid'=>'required|integer|min:1', 'example'=>'2', 'desc'=>'店铺ID'],
        	'gift_id'        => ['type'=>'int',    'valid'=>'required',               'example'=>'',  'desc'=>'赠品促销id'],
            'gift_name'      => ['type'=>'string', 'valid'=>'required',               'example'=>'',  'desc'=>'赠品促销名称',   'msg'=>'请填写赠品促销名称'],
            'limit_quantity' => ['type'=>'string', 'valid'=>'required|integer|min:1', 'example'=>'',  'desc'=>'满足条件数量',   'msg'=>'请填写促销条件|赠品促销条件数量必须为正整数|赠品促销条件数量必须为正整数'],
            'gift_desc'      => ['type'=>'string', 'valid'=>'required|max:50',        'example'=>'',  'desc'=>'赠品规则描述',   'msg'=>'请填写赠品描述|赠品描述最多50个字'],
            'valid_grade'    => ['type'=>'string', 'valid'=>'required',               'example'=>'',  'desc'=>'适用的会员等级', 'msg'=>'请选择会员等级'],
            'start_time'     => ['type'=>'string', 'valid'=>'required',               'example'=>'',  'desc'=>'赠品促销开始时间'],
            'end_time'       => ['type'=>'string', 'valid'=>'required',               'example'=>'',  'desc'=>'赠品促销结束时间'],

            'gift_item'        => ['type'=>'jsonArray', 'valid'=>'required', 'example'=>'', 'desc'=>'赠品sku', 'params'=>[
                'sku_id'   => ['type'=>'int', 'valid'=>'required|integer|min:1', 'example'=>'2', 'desc'=>'SKU ID'],
                'withoutReturn'   => ['type'=>'bool', 'valid'=>'', 'example'=>'1', 'desc'=>'在退货的时候可以不用归还赠品'],
                'quantity' => ['type'=>'int', 'valid'=>'required|integer|min:1', 'example'=>'1', 'desc'=>'赠送赠品的件数', 'msg'=>'请添加赠品赠送数量|赠品赠送数量必须为正整数|赠品赠送数量必须为正整数'],
            ]],

            'gift_rel_item' => ['type'=>'jsonArray', 'valid'=>'required',  'example'=>'',  'desc'=>'赠品促销关联的商品', 'params'=>array(
                'item_id' => ['type'=>'int',    'valid'=>'required|integer|min:1', 'example'=>'', 'desc'=>'商品ID'],
                'sku_id'  => ['type'=>'string', 'valid'=>'', 'example'=>'', 'desc'=>'SKU ID以逗号隔开, 数据为空则表示所有规格都参加促销'],
            ), 'msg'=>'请选择促销商品'],
        );

        return $return;
    }

    /**
     *  添加组合促销促销数据
     * @param  array $apiData 组合促销促销各种值
     * @return
     */
    public function giftUpdate($apiData)
    {
        $apiData['gift_name'] = strip_tags($apiData['gift_name']);
        $apiData['gift_desc'] = strip_tags($apiData['gift_desc']);

        $forPlatform = intval($apiData['used_platform']);
        $apiData['used_platform'] = $forPlatform ? $forPlatform : '0';;

        return kernel::single('syspromotion_data_object')
            ->setPromotion('gift', $apiData['shop_id'])
            ->savePromotion($apiData);
    }
}
