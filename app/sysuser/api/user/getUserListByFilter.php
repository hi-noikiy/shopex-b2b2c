<?php
class sysuser_api_user_getUserListByFilter
{

    public $apiDescription = "获取用户的列表(危险方法，只能用于内部某些东西)";

    public function getParams()
    {
        $return['params'] = array(
            'filter' => ['type'=>'json','valid'=>'required', 'description'=>'会员筛选器','default'=>'','example'=>''],
            'fields' => ['type'=>'field_list','valid'=>'required', 'description'=>'查询字段','default'=>'','example'=>''],
            'page_no' => ['type'=>'int','valid'=>'numeric','description'=>'分页当前页码,1<=no<=499','example'=>'','default'=>'1'],
            'page_size' =>['type'=>'int','valid'=>'numeric','description'=>'分页每页条数(1<=size<=200)','example'=>'','default'=>'40'],
        );
        return $return;
    }

    /**
     * @return array 'user_list' 详细数据
     * @return int   'count'     共多少条
     * @return int   'page_size' 每页多少条
     * @return int   'page_num'  共多少页
     */
    public function getList($params)
    {
        $filter = json_decode($params['filter'], 1);
        $fields = $params['fields'];
        $pageNo = $params['page_no'];
        $pageSize = $params['page_size'];

        $page =  $pageNo ? $pageNo : 1;
        $limit = $pageSize ? $pageSize : 100;
        $offset = ($page - 1) * $pageSize;

        $users = app::get('sysuser')->model('user')->getList($fields, $filter, $offset, $limit);
        $count = app::get('sysuser')->model('user')->count($filter);

        return [
            'user_list' => $users,
            'count'     => $count,
            'page_size' => $limit,
            'page_num'  => $count > 0 ? intval(  $count / $limit + 1 ) : 0,
        ];
    }
}

