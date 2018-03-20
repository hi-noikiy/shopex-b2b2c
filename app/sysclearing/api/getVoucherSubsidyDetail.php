<?php
/**
 * clearing.subsidy.voucher.detail.list
 */
class sysclearing_api_getVoucherSubsidyDetail {

    public $apiDescription = "获取购物券补贴明细";

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'shop_id'   => ['type'=>'string', 'valid'=>'required', 'example'=>'','description'=>'店铺编号id'],
            'voucher_id'=> ['type'=>'string', 'valid'=>'',  'example'=>'','description'=>'购物券ID'],
            'subsidy_time_than' => ['type'=>'string', 'valid'=>'', 'desc'=>'生成补贴明细单开始时间'],
            'subsidy_time_lthan' => ['type'=>'string', 'valid'=>'','desc'=>'生成补贴明细单结束时间'],
            'type'      => ['type'=>'string', 'valid'=>'',  'example'=>'1','description'=>'补贴类型 1未补贴，2已补贴'],
            'page_no'   => ['type'=>'int',     'valid'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int',     'valid'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'orderBy'   => ['type'=>'string',  'valid'=>'', 'example'=>'', 'description'=>'排序，默认subsidy_time asc'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
        );
        return $return;
    }

    public function handle($params)
    {
        if($params['type'])
        {
            $filter['type'] = $params['type'];
        }

        if($params['subsidy_time_than'])
        {
            $filter['subsidy_time|than']  = $params['subsidy_time_than'];
        }
        if($params['subsidy_time_lthan'])
        {
            $filter['subsidy_time|lthan']  = $params['subsidy_time_lthan'];
        }

        if( $params['voucher_id'] )
        {
            $filter['voucher_id'] = $params['voucher_id'];
        }

        $filter['shop_id'] = $params['shop_id'];

        $objMdlSettleDetail = app::get('sysclearing')->model('vouchersubsidy_detail');
        $count = $objMdlSettleDetail->count($filter);

        $data = array();
        if( $count )
        {
            $pageTotal = ceil($count/$params['page_size']);
            $page =  $params['page_no'] ? $params['page_no'] : 1;
            $limit = $params['page_size'] ? $params['page_size'] : 10;
            $currentPage = ($pageTotal < $page) ? $pageTotal : $page; //防止传入错误页面，返回最后一页信息
            $offset = ($currentPage-1) * $limit;

            $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' subsidy_time desc';
            $data = $objMdlSettleDetail->getList('*', $filter,$offset,$limit,$orderBy);
        }
        $result['list'] = $data;
        $result['pagers']['total'] = $count;

        return $result;
    }
}

