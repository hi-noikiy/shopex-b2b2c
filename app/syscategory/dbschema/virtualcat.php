<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

return array(
    'columns' => array(
        'virtual_cat_id' => array(
            'type' => 'number',
            'required' => true,
            //'pkey' => true,
            'autoincrement' => true,
            'comment' => app::get('syscategory')->_('虚拟分类ID'),
            'width' => 110,
        ),
        'virtual_parent_id' => array(
            'type' => 'number',
            'comment' => app::get('syscategory')->_('虚拟分类父级ID'),
            'width' => 110,
        ),
        'virtual_cat_name' => array(
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'required' => true,
            'is_title' => true,
            'default' => '',
            'label' => app::get('syscategory')->_('虚拟分类名称'),
            'width' => 110,
            'searchtype' => 'has',
            'in_list' => true,
            'default_in_list' => true,
        ),
        'virtual_cat_logo' => array(
            'type' => 'string',
            'comment' => app::get('syscategory')->_('一级分类logo'),
        ),
        'url'=>array(
            'label'=>app::get('syscategory')->_('自定义分类链接地址'),
            'type' => 'serialize',
            'width'=>300,
            'in_list'=>false,
        ),
        'cat_path' => array(
            //'type' => 'varchar(100)',
            'type' => 'string',
            'length' => 100,
            'default' => ',',
            'comment' => app::get('syscategory')->_('分类路径(从根至本结点的路径,逗号分隔,首部有逗号)'),
            'width' => 110,
        ),
        'level' => array(
            'type' => array(
                '1' => app::get('syscategory')->_('一级分类'),
                '2' => app::get('syscategory')->_('二级分类'),
                '3' => app::get('syscategory')->_('三级分类'),
            ),
            'default' => '1',
            'label' => app::get('syscategory')->_('分类层级'),
            'width' => 110,
            'in_list' => true,
            'default_in_list' => false,
        ),
        'is_leaf' => array(
            'type' => 'bool',
            'required' => true,
            'default' => 0,
            'comment' => app::get('syscategory')->_('是否叶子结点（true：是；false：否）'),
            'width' => 110,
        ),
        'filter' => array(
            'type' => 'text',
            'editable' => false,
            'comment' => app::get('syscategory')->_('筛选条件'),
        ),
        'addon' => array(
            'type' => 'text',
            'editable' => false,
            'comment' => app::get('syscategory')->_('附加项'),
        ),
        'child_count' => array(
            'type' => 'number',
            'default' => 0,
            'required' => true,
            'editable' => false,
            'comment' => app::get('syscategory')->_('子类别数量'),
        ),
        'order_sort' => array(
            'type' => 'number',
            'label' => app::get('syscategory')->_('排序'),
            'width' => 110,
            'default' => 0,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'virtual_cat_template' => array(
            'type' => 'string',
            'length' => 50,
            'comment' => app::get('syscategory')->_('虚拟类目模板'),
        ),
        'modified_time' => array(
            'type' => 'last_modify',
            'label' => app::get('syscategory')->_('更新时间'),
            'width' => 110,
            'in_list' => true,
            'orderby' => true,
        ),
        'platform' => array(
            'type' => array(
                'pc' => app::get('syscategory')->_('电脑端'),
                'h5' => app::get('syscategory')->_('移动端'),
                'app' => app::get('syscategory')->_('APP端')
            ),
            'required' => true,
            'default'  => 'pc',
            'in_list' => true,
            'default_in_list' => true,
            'label' => app::get('syscontent')->_('使用终端'),
            'order'=>3,
        ),
    ),
    'primary' => 'virtual_cat_id',
    'index' => array(
        'ind_cat_path' => ['columns' => ['cat_path']],
        'ind_virtual_cat_name' => ['columns' => ['virtual_cat_name']],
        'ind_modified_time' => ['columns' => ['modified_time']],
        'ind_ordersort' => ['columns' => ['order_sort']],
    ),
    'comment' => app::get('syscategory')->_('商品虚拟分类表'),
);
