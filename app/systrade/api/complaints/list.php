<?php
/**
 * list.php
 *
 * @author     Xiaodc
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class systrade_api_complaints_list {

    public $apiDescription = '获取指定会员订单投诉列表';

    public function getparams()
    {
        //接口传入的参数
        $return['params'] = array(
                'user_id' => ['type'=>'int', 'valid'=>'required', 'default'=>'', 'example'=>'23','description'=>'会员id'],
                'page_no' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'分页当前页数,默认为1'],
                'page_size' => ['type'=>'int','valid'=>'', 'default'=>'', 'example'=>'', 'description'=>'每页数据条数,默认100条'],
                'fields'=> ['type'=>'field_list','valid'=>'required', 'default'=>'', 'example'=>'', 'description'=>'获取订单投诉的字段'],
        );

        return $return;
    }

    public function getList($params)
    {
        //分页使用
        $pageSize = $params['page_size'] ? $params['page_size'] : 40;
        $pageNo = $params['page_no'] ? $params['page_no'] : 1;

        $limit = $pageSize;
        $offset = ($pageNo-1)*$limit;


        $objMdlComplaints = app::get('systrade')->model('order_complaints');
        $countTotal = $objMdlComplaints->count(array('user_id' => $params['user_id']));

        $data = array();
        if( $countTotal )
        {
            $pageTotal = ceil($countTotal/$params['page_size']);
            $page =  $params['page_no'] ? $params['page_no'] : 1;
            $limit = $params['page_size'] ? $params['page_size'] : 10;
            $currentPage = $pageTotal < $page ? $pageTotal : $page;
            $offset = ($currentPage-1) * $limit;

            $data['list'] = $objMdlComplaints->getList($params['fields'], array('user_id' => $params['user_id']), $offset, $limit, 'complaints_id desc');
            $data['count'] = $countTotal;
        }

        return $data;
    }
}

