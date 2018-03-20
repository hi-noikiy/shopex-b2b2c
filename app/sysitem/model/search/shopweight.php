<?php

class sysitem_mdl_search_shopweight extends dbeav_model {

    public function count($filter=null)
    {
        return app::get('sysshop')->model('shop')->count($filter);
    }

    /**
     * @brief 获取表名称
     *
     * @param bool $real 是否返回表的全名
     *
     * @return string
     */
    public function table_name($real=false)
    {
        if($real)
        {
            return $this->app->app_id.'_search_shopweight';
        }
        else
        {
            return 'search_shopweight';
        }
    }

    public function get_schema()
    {
        $schema = array (
            'columns' => array (
                'shop_id' => array (
                    'type' => 'number',
                    'label' => app::get('search')->_('店铺ID'),
                    'comment' => app::get('search')->_('店铺ID'),
                ),
                'shop_name' => array (
                    'type' => 'string',
                    'label' => app::get('search')->_('店铺名称'),
                    'comment' => app::get('search')->_('店铺名称'),
                ),
                'shop_type' => array (
                    'type' => 'string',
                    'label' => app::get('search')->_('店铺类型'),
                ),
                'shoptype_weight' => array (
                    'type' => 'string',
                    'label' => app::get('search')->_('类型权重得分'),
                ),
                'custom_weight' => array (
                    'type' => 'string',
                    'label' => app::get('search')->_('店铺自定义权重'),
                ),
            ),
            'idColumn' => 'shop_id',
            'in_list' => array (
                0 => 'shop_name',
                1 => 'shop_type',
                2 => 'shoptype_weight',
                3 => 'custom_weight',
            ),
            'default_in_list' => array (
                0 => 'shop_name',
                1 => 'shop_type',
                2 => 'shoptype_weight',
                3 => 'custom_weight',
            ),
        );
        return $schema;
    }

    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $shoptype = array(
            'flag'=>'品牌旗舰店',
            'brand'=>'品牌专卖店',
            'cat'=>'类目专营店',
            'self'=>'运营商自营店铺',
            'store'=>'多品类通用型店铺',
        );
        $searchweight = config::get('searchweight.shop');
        $objMdlItemSearchShopweight = app::get('sysitem')->model('item_search_shopweight');
        $data = app::get('sysshop')->model('shop')->getList('shop_id,shop_name,shop_type', $filter, $offset, $limit, $orderType);
        foreach ($data as &$v) {
            $tmp = $objMdlItemSearchShopweight->getRow('*', ['shop_id'=>$v['shop_id']]);
            $v['shoptype_weight'] = $searchweight['shoptype_'.$v['shop_type']];
            $v['shop_type'] = $shoptype[$v['shop_type']];
            $v['custom_weight'] = $tmp['custom_weight'];
        }
        return $data;
    }
}//End Class

