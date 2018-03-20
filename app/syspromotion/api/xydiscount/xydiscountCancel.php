<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 取消单条X件Y折促销
 */
final class syspromotion_api_xydiscount_xydiscountCancel {

    public $apiDescription = '取消单条X件Y折促销';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'店铺ID必填'],
            'xydiscount_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'X件Y折促销ID必填'],
        );

        return $return;
    }

    /**
     * @brief 根据X件Y折促销ID取消X件Y折促销
     * @author lujy
     * @param $params array
     *
     * @return
     */
    public function xydiscountCancel($params)
    {
        return kernel::single('syspromotion_data_object')->setPromotion('xydiscount', $params['shop_id'])->cancelPromotion($params['xydiscount_id']);
    }

}

