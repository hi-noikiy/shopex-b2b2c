<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 满减促销规则应用
 * promotion.fullminus.apply
 */
final class syspromotion_api_fullminus_fullminusApply {

    public $apiDescription = '满减促销规则应用';

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'user_id'                => ['type'=>'int',   'valid'=>'required|integer|min:1', 'description'=>'用户id'],
            'grade_id'               => ['type'=>'int',   'valid'=>'required|integer|min:1', 'description'=>'用户等级id'],
            'fullminus_id'           => ['type'=>'int',   'valid'=>'required|integer|min:1', 'description'=>'满减促销表id'],
            'promotion_id'           => ['type'=>'int',   'valid'=>'required|integer|min:1', 'description'=>'促销关联表id'],
            'forPromotionTotalPrice' => ['type'=>'float', 'valid'=>'',                       'description'=>'符合应用促销的商品总价'],
        );

        return $return;
    }

    /**
     *  满减促销规则应用
     * @param  array $params 筛选条件数组
     * @return array         返回一条促销详情
     */
    public function fullminusApply($params)
    {
        $data = array(
            'user_id' => $params['user_id'],
            'grade_id' => $params['grade_id'],
            'fullminus_id'=>$params['fullminus_id'],
            'promotion_id' => $params['promotion_id'],
            'forPromotionTotalPrice' => $params['forPromotionTotalPrice'],
        );
        $discount_price = kernel::single('syspromotion_solutions_fullminus')->apply($data);

        return $discount_price;
    }


}

