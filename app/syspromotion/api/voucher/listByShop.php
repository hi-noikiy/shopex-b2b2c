<?php
/**
 * promotion.voucher.shop.list.get
 *
 * ShopEx licence
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 *
 * 商家获取购物券列表
 */
final class syspromotion_api_voucher_listByShop {

    public $apiDescription = '商家获取购物券列表';

    public $use_strict_filter = true;

    public function getParams()
    {
        $return['params'] = array(
            'voucher_id'=> ['type'=>'string','valid'=>'','desc'=>'购物券ID,多个逗号隔开','example'=>'1',],
            'shop_id' => ['type'=>'int','valid'=>'required','desc'=>'店铺ID','example'=>'1',],
            'is_apply' => ['type'=>'int','valid'=>'boolean','desc'=>'店铺是否申请过当前购物券','example'=>1,],
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
    public function get($params)
    {
        if( $params['is_apply'] )
        {
            $result = $this->__getApplyList($params);
        }
        else
        {
            $result = $this->__getNotApplyList($params);
        }

        return $result;
    }

    private function __getApplyList($params)
    {
        $objMdlVoucherRegister = app::get('syspromotion')->model('voucher_register');
        $filter['shop_id'] = $params['shop_id'];
        if( $params['voucher_id'] )
        {
            $filter['voucher_id|in'] = explode(',',$params['voucher_id']);
        }

        $total = $objMdlVoucherRegister->count($filter);

        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $page =  $params['page_no'] ? $params['page_no'] : 1;

        $pageTotal = ceil($total/$params['page_size']);
        $currentPage = $pageTotal < $page ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : 'created_time DESC';
        $registerData = $objMdlVoucherRegister->getList('valid_status,verify_status,voucher_id', $filter, $offset, $limit, $orderBy);
        $voucherId = array_column($registerData,'voucher_id');
        if( $voucherId )
        {
            $registerData = array_bind_key($registerData, 'voucher_id');
            $data = app::get('syspromotion')->model('voucher')->getList($params['fields'], ['voucher_id|in'=>$voucherId], 0, $limit, $orderBy);
            foreach( $data as &$value )
            {
                $value['register'] = $registerData[$value['voucher_id']];
            }
        }

        $result['list'] = $data;
        $result['pagers']['total'] = $total;

        return $result;
    }

    private function __getNotApplyList($params)
    {
        $objMdlVoucher = app::get('syspromotion')->model('voucher');
        $filter = array();
        $total = $objMdlVoucher->count($filter);
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $page =  $params['page_no'] ? $params['page_no'] : 1;

        $pageTotal = ceil($total/$params['page_size']);
        $currentPage = $pageTotal < $page ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' created_time DESC';
        $data = $objMdlVoucher->getList($params['fields'], $filter, $offset, $limit, $orderBy);

        $voucherId = array_column($data,'voucher_id');
        if( $voucherId )
        {
            $registerData = app::get('syspromotion')->model('voucher_register')->getList('valid_status,verify_status,voucher_id', ['voucher_id|in'=>$voucherId, 'shop_id'=>$params['shop_id']]);
            $registerData = array_bind_key($registerData, 'voucher_id');

            foreach( $data as &$value)
            {
                if( $registerData[$value['voucher_id']] )
                {
                    $value['register'] = $registerData[$value['voucher_id']];
                }
            }
        }

        $result['list'] = $data;
        $result['pagers']['total'] = $total;

        return $result;
    }
}

