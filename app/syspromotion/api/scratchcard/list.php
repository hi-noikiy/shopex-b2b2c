<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 获取刮刮卡列表
 */
final class syspromotion_api_scratchcard_list {

    public $apiDescription = '获取刮刮卡列表';
    public $use_filter_strict = true; //使用严格过滤

    public function getParams()
    {
        $return['params'] = array(
            'page_no' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'需要的字段','default'=>'','example'=>''],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'排序，默认created_time asc'],
            'scratchcard_id' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'刮刮卡id'],
            'scratchcard_name' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'刮刮卡名称'],
            'status' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'刮刮卡状态'],
            'used_platform' => ['type'=>'int', 'valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'刮刮卡适用平台'],
        );

        return $return;
    }


    /**
     * 获取刮刮卡列表
     */
    public function scratchcardList($params)
    {
        $objMdlscratchcard = app::get('syspromotion')->model('scratchcard');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }

        // 平台未选择则默认全选
        $filter['used_platform'] = $params['used_platform'];
        if( $params['used_platform'] == '1' )
        {
            $filter['used_platform'] = array('0', '1');
        }
        elseif( $params['used_platform'] == '2' )
        {
            $filter['used_platform'] = array('0', '2');
        }

        if($params['status']){
            $filter['status'] = $params['status'];
        }

        $scratchcardTotal = $objMdlscratchcard->count($filter);
        $pageTotal = ceil($scratchcardTotal/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 1000;
        $currentPage = $pageTotal < $page ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' scratchcard_id DESC';
        $scratchcardData = $objMdlscratchcard->getList($params['fields'], $filter, $offset, $limit, $orderBy);
        $result = array(
            'data' => $scratchcardData,
            'total' => $scratchcardTotal,
        );

        return $result;
    }
}

