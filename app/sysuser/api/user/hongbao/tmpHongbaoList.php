<?php
/**
 * ShopEx licence
 * - user.hongbao.list.get
 * - 获取用户红包列表接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class sysuser_api_user_hongbao_tmpHongbaoList {

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取用户在暂存区的红包列表接口';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'   => ['type'=>'string', 'valid'=>'required',  'desc'=>'用户ID'],
            'page_no'   => ['type'=>'int', 'valid'=>'numeric', 'example'=>'1', 'desc'=>'分页当前页数,默认为1'],
            'page_size' => ['type'=>'int', 'valid'=>'numeric', 'example'=>'10', 'desc'=>'每页数据条数,默认20条'],
            'fields'    => ['type'=>'field_list', 'valid'=>'required', 'example'=>'hongbao_id,hongbao_name', 'desc'=>'需要返回的字段'],
        );
        return $return;
    }

    /**
     * 获取用户红包列表接口
     *
     * @desc 获取用户红包列表接口
     * @return bool true
     */
    public function get($params)
    {

        $filter['user_id'] = $params['user_id'];

        $objMdlUserHongbao = app::get("sysuser")->model('user_hongbao_tmp');
        $total =  $objMdlUserHongbao->count($filter);

        if( $total )
        {
            $pageNo = $params['page_no'] ? $params['page_no'] : 1;
            $pageSize = $params['page_size'] ? $params['page_size'] : 10;

            $pageTotal = ceil($total/$pageSize);
            $currentPage = ($pageTotal < $pageNo) ? $pageTotal : $pageNo; //防止传入错误页面，返回最后一页信息

            $offset = ($currentPage-1) * $pageSize;

            $return['list'] = app::get("sysuser")->model('user_hongbao_tmp')->getList($params['fields'], $filter, $offset, $pageSize);
            $return['pagers']['total'] = $total;
            return $return;
        }
        else
        {
            return null;
        }
    }
}

