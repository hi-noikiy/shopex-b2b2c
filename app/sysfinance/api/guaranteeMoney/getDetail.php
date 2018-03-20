<?php
/**
 * ShopEx licence
 * sysfinance.guaranteeMoney.get
 * - 用于获取单店保证金详情
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 
 */
class sysfinance_api_guaranteeMoney_getDetail {
    public $apiDescription = "用于获取单店保证金详情";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'integer','valid'=>'required|min:1','description'=>'店铺id'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的字段集'],
        );
        return $return;
    }

    public function get($params){
        $objMdlGuranteeMoney = app::get('sysfinance')->model('guaranteeMoney');

        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        
        $data = $objMdlGuranteeMoney->getRow($params['fields'], ['shop_id'=>$params['shop_id']]);

        return $data;
    }

}