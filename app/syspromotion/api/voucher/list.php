<?php
/**
 * promotion.voucher.list.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取购物券列表
 */
final class syspromotion_api_voucher_list {

    public $apiDescription = '获取购物券列表';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id'=> ['type'=>'string','valid'=>'','desc'=>'购物券ID,多个逗号隔开','example'=>'1',],
            'page_no' => ['type'=>'int','valid'=>'integer','desc'=>'分页当前页码,1<=no<=499','example'=>'1',],
            'page_size' =>['type'=>'int','valid'=>'integer','desc'=>'分页每页条数(1<=size<=200)','example'=>'20'],
            'order_by' => ['type'=>'int','valid'=>'','desc'=>'排序方式','example'=>'created_time desc'],
            'fields' => ['type'=>'field_list', 'valid'=>'required', 'example'=>'', 'description'=>'查询字段'],
        );

        return $return;
    }

    /**
     *  获取购物券列表
     * @return
     */
    public function handle($params)
    {
        if( $params['voucher_id'] )
        {
            $filter['voucher_id|in'] = explode(',',$params['voucher_id']);
        }

        $total = app::get('syspromotion')->model('voucher')->count($filter);
        $data = [];
        if( $total )
        {
            $limit = $params['page_size'] ? $params['page_size'] : 10;
            $page =  $params['page_no'] ? $params['page_no'] : 1;

            $pageTotal = ceil($total/$params['page_size']);
            $currentPage = $pageTotal < $page ? $pageTotal : $page;
            $offset = ($currentPage-1) * $limit;

            $orderBy  = $params['orderBy'] ? $params['orderBy'] : 'created_time DESC';
            $data = app::get('syspromotion')->model('voucher')->getList($params['fields'], $filter, 0, $limit, $orderBy);
        }
        $result['list'] = $data;
        $result['pagers']['total'] = $total;

        return $result;
    }
}

