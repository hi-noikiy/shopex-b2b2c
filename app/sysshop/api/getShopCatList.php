<?php
class sysshop_api_getShopCatList{

    public $apiDescription = "获取店铺自有类目(普通列表)";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = array(
            'shop_id' => ['type'=>'integer','valid'=>'required|integer|min:1','description'=>'店铺id',],
            'cat_id' => ['type'=>'string','valid'=>'','description'=>'分类id,用逗号隔开，逗号是半角的，例如  3,5,6'],
            'parent_id'=> ['type'=>'integer','valid'=>'','description'=>'分类父级id'],
            'level'=> ['type'=>'integer','valid'=>'','description'=>'分类的级别'],
            'is_leaf'=> ['type'=>'boolean','valid'=>'in:0,1','description'=>'是否叶子节点'],
            'cat_name'=> ['type'=>'string','valid'=>'','description'=>'分类名称'],

            'page_no' => ['type'=>'integer', 'valid'=>'', 'description'=>'分页当前页数,默认为1,页码从1开始'],
            'page_size' => ['type'=>'integer', 'valid'=>'', 'description'=>'每页数据条数,默认10条'],
            'fields'    => ['type'=>'field_list', 'valid'=>'', 'description'=>'需要的字段'],
            'orderBy' => ['type'=>'string', 'valid'=>'', 'description'=>'排序，默认按照添加顺序排序'],
        );
        return $return;
    }
    public function getShopCatList($params)
    {
        $objMdlShopCat = app::get('sysshop')->model('shop_cat');
        if(!$params['fields'])
        {
            $params['fields'] = '*';
        }
        $filter['shop_id'] = $params['shop_id'];
        if($params['cat_id'])
        {
            $filter['cat_id'] = explode(',', $params['cat_id']);
        }

        $shopCatCount = $objMdlShopCat->count($filter);
        if(!$shopCatCount)
        {
            $result = array(
                    'data' => array(),
                    'count' => 0,
            );

            return $result;
        }

        $pageParams = utils::_format_page($shopCatCount, $params['page_no'], $params['page_size']);

        $orderBy  = $params['orderBy'] ? $params['orderBy'] : ' cat_id DESC';
        $shopCatData = $objMdlShopCat->getList($params['fields'], $filter, $pageParams['offset'], $pageParams['limit'], $orderBy);
        $result = array(
            'data' => $shopCatData,
            'count' => $shopCatCount,
        );

        return $result;
    }
}
