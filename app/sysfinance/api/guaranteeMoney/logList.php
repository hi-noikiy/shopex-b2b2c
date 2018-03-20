<?php
/**
 * ShopEx licence
 * sysfinance.guaranteeMoney.get
 * - 获取保证金操作列表
 * @copyright Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license   http://ecos.shopex.cn/ ShopEx License
 * @link      http://www.shopex.cn/
 * @author    shopex 
 */
class sysfinance_api_guaranteeMoney_logList {
    public $apiDescription = "获取保证金操作列表";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'integer','valid'=>'required|min:1','description'=>'店铺id'],
            'fields' => ['type'=>'field_list','valid'=>'','description'=>'要获取的字段集'],
            'op_type' => ['type'=>'string','valid'=>'','description'=>'交易类型(充值或者扣款)'],
            'created_time_start'=>['type'=>'string','valid'=>'','description'=>'查询指定时间内的操作记录开始yyyy-MM-dd'],
            'created_time_end'=>['type'=>'string','valid'=>'','description'=>'查询指定时间内的操作记录结束yyyy-MM-dd'],
            'page_no' => ['type'=>'int', 'valid'=>'int','description'=>'分页当前页数，默认为1'],
            'page_size' => ['type'=>'int','valid'=>'int','description'=>'每页数据条数，默认40条'],
            'orderBy' => ['type'=>'string','valid'=>'','description'=>'排序'],

        );
        return $return;
    }

    public function getList($params){
        $objMdlGuranteeMoneyLog = app::get('sysfinance')->model('guaranteeMoney_oplog');

        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }

        if($params['shop_id'])
        {
            $filter['shop_id'] = $params['shop_id'];
        }

        if ($params['op_type']) {
            $filter['op_type'] = $params['op_type'];
        }

        if($params['created_time_start'])
        {
            $filter['created_time|bthan'] = $params['created_time_start'];
            unset($params['created_time_start']);
        }
        if($params['created_time_end'])
        {
            $filter['created_time|lthan'] = $params['created_time_end'];
            unset($params['created_time_end']);
        }


        $count = $objMdlGuranteeMoneyLog->count($filter);
        $pageTotal = ceil($count/$params['page_size']);
        $page = $params['page_no']?$params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] :40;
        $currentPage = ($pageTotal < $page) ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;
        $orderBy = $params['orderBy'] ? $params['orderBy'] : 'created_time DESC';
        $data['list'] = $objMdlGuranteeMoneyLog->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $data['count'] = $count;

        return $data;
    }

}