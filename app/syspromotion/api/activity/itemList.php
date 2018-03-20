<?php
// promotion.activity.item.list
class syspromotion_api_activity_itemList{
    public $apiDescription = "获取参加活动的商品";
    public function getParams()
    {
        $data['params'] = array(
            'id'          => ['type'=>'string', 'valid'=>'', 'description'=>'主键id'],
            'activity_id' => ['type'=>'string', 'valid'=>'', 'description'=>'活动id,多个用“,”隔开'],
            'cat_id'      => ['type'=>'string', 'valid'=>'', 'description'=>'类目id,多个用“,”隔开'],
            'item_id'     => ['type'=>'string', 'valid'=>'', 'description'=>'商品id，多个用“,”隔开'],
            'status'      => ['type'=>'string', 'valid'=>'', 'description'=>'活动状态'],
            'start_time'  => ['type'=>'string', 'valid'=>'in:sthan,bthan', 'description'=>'与开始时间相比，大于或小于指定时间,值为(sthan、bthan)'],
            'end_time'    => ['type'=>'string', 'valid'=>'in:sthan,bthan', 'description'=>'与开结束相比，大于或小于指定时间,值为(sthan、bthan)'],
            'time'        => ['type'=>'string', 'valid'=>'date', 'description'=>'指定时间如(2015-14-04)'],
            'shop_id'     => ['type'=>'int',    'valid'=>'int',  'description'=>'店铺id'],

            'page_no'   => ['type'=>'int',        'valid'=>'int',    'description'=>'分页当前页码,1<=no<=499'],
            'page_size' => ['type'=>'int',        'valid'=>'int',    'description'=>'分页每页条数(1<=size<=200)'],
            'order_by'  => ['type'=>'int',        'valid'=>'string', 'description'=>'排序方式,默认 item_id desc'],
            'fields'    => ['type'=>'field_list', 'valid'=>'string', 'description'=>'查询字段，默认*'],
        );
        return $data;
    }
    public function getList($params)
    {
        $row = $params['fields'] ? $params['fields'] :"*";
        $filter = array();

        $columnIds = ['id','activity_id','cat_id','item_id'];

        foreach( $columnIds as $id )
        {
            if($params[$id])
            {
                $filter[$id] = explode(',',$params[$id]);
            }
        }

        if($params['shop_id']!='')
        {
            $filter['shop_id'] = $params['shop_id'];
        }

        if($params['status'])
        {
            $filter['verify_status'] = $params['status'];
        }

        if($params['start_time'])
        {
            $filter['start_time|'.$params['start_time']] = $params['time'] ? strtotime($params['time']) : time();
        }

        if($params['end_time'])
        {
            $filter['end_time|'.$params['end_time']] = $params['time'] ? strtotime($params['time']) : time();
        }

        $objActivityItem = kernel::single('syspromotion_activity');

        //统计总数
        $data['count'] = $objActivityItem->countActivityItem($filter);

        //分页使用
        $pageTotal = ceil($data['count']/$params['page_size']);
        $page =  $params['page_no'] ? $params['page_no'] : 1;
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $currentPage = $pageTotal < $page ? $pageTotal : $page;
        $offset = ($currentPage-1) * $limit;

        $orderBy = $params['order_by'];
        if(!$params['order_by'])
        {
            $orderBy = "item_id desc";
        }

        $data['list'] = $objActivityItem->getItemList($row,$filter,$offset, $limit,$orderBy);

        return $data;
    }
}
