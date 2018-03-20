<?php
/**
 * ShopEx licence
 * sysfinance.guaranteeMoney.logdetail.get
 * - 获取单条保证金操作记录详情
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 
 */
class sysfinance_api_guaranteeMoney_logdetail {
    public $apiDescription = "获取保证金操作列表";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'op_id' => ['type'=>'integer','valid'=>'required|min:1','description'=>'操作id'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的字段集'],
        );
        return $return;
    }

    public function get($params){
        $objMdlGuranteeMoneyLog = app::get('sysfinance')->model('guaranteeMoney_oplog');

        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }

        $data = $objMdlGuranteeMoneyLog->getRow($params['fields'], ['op_id'=>$params['op_id']]);

        return $data;
    }

}