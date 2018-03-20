<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

return array (
    'columns' =>
    array (
        'node_id' =>array (
            'type' => 'number',
            'required' => true,
            'comment'=> app::get('syscontent')->_('节点id'),
            'label'=> app::get('syscontent')->_('节点id'),
            'autoincrement' => true,
            'width' => 10,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'parent_id' =>array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'comment'=> app::get('syscontent')->_('父节点'),
            'label'=> app::get('syscontent')->_('父节点'),
            'width' => 10,
            'in_list' => true,
        ),
        'node_depth' => array(
            'type' => 'smallint',
            'required' => true,
            'default' => 0,
            'comment' => app::get('syscontent')->_('节点深度'),
            'label' => app::get('syscontent')->_('节点深度'),
        ),
        'node_name' => array(
            'type' => 'string',
            'required' => true,
            'default'=>'',
            'comment'=> app::get('syscontent')->_('节点名称'),
            'label'=> app::get('syscontent')->_('节点名称'),
            'is_title' => true,
            'in_list' => true,
            'default_in_list' => true,
        ),
        'node_path'=>array (
            'type' => 'string',
            'comment'=> app::get('syscontent')->_('节点路径'),
            'in_list' => false,
        ),
        'has_children' => array(
            'type' => 'bool',
            'default' => 0,
            'required' => true,
            'comment' => app::get('syscontent')->_('是否存在子节点'),
            'label' => app::get('syscontent')->_('是否存在子节点'),
            'in_list' => true,
            'default_in_list' => true,
        ),
        'ifpub'=>array (
            'type' => 'bool',
            'default' => 0,
            'required' => true,
            'comment' => app::get('syscontent')->_('发布'),
            'label' => app::get('syscontent')->_('是否发布'),
            'in_list' => true,
        ),
        'order_sort'=> array (
            'type' => 'number',
            'required' => true,
            'default' => 0,
            'comment' => app::get('syscontent')->_('排序'),
            'label' => app::get('syscontent')->_('排序'),
        ),
        'modified'=> array (
            'type' => 'time',
            'comment' => app::get('syscontent')->_('修改时间'),
            'label' => app::get('syscontent')->_('修改时间'),
            'default_in_list' => true,
            'in_list' => true,
        ),

    ),
    'primary' => 'node_id',
    'index' => array(
        'ind_node_name' => ['columns' => ['node_name']],
        'ind_order_sort' => ['columns' => ['order_sort']],
    ),
    'comment' => app::get('syscontent')->_('文章节点表'),
);
