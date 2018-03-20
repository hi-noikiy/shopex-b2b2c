<?php
/**
 * -- sysdecorate.nav.add
 * -- 添加文本导航挂件
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysdecorate_api_nav_add {

    public $apiDescription = '添加文本导航挂件';

    public $use_strict_filter = true; // 是否严格过滤参数

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'widgets_id' => ['type'=>'int',       'valid'=>'integer', 'desc'=>'挂件ID'],
            'shop_id'    => ['type'=>'int',       'valid'=>'required|integer', 'desc'=>'店铺ID'],
            'page_name'  => ['type'=>'string',    'valid'=>'required', 'desc'=>'页面名称'],
            'platform'   => ['type'=>'string',    'valid'=>'required|in:pc,wap,app', 'desc'=>'平台名称'],
            'order_sort' => ['type'=>'int',       'valid'=>'required|integer', 'desc'=>'排序'],
            'list'       => ['type'=>'jsonArray', 'valid'=>'required', 'desc'=>'文本导航数据集合', 'msg'=>'至少添加一项导航菜单栏目类型','params'=>array(
                'name'   => ['type' => 'string', 'valid' => 'required|max:8', 'desc' => '导航菜单', 'msg'=>'请填写导航菜单|导航菜单不能大于8个字'],
                'type'       => ['type'=>'string',     'valid'=>'required', 'desc'=>'栏目名称'],
                'item_ids' => ['type' => 'string', 'valid' => '', 'desc' => '导航中选择的商品Id集合，逗号隔开', 'msg'=>'请选择导航菜单商品'],
                'navlink'    => ['type'=>'string', 'valid'=>'',   'desc'=>'链接地址'],
                'promotion_id'=> ['type'=>'int',   'valid'=>'',   'desc'=>'活动id'],
            )],
        );
        return $return;
    }

    private function __checkUrl($url)
    {
        if( $url )
        {
            if( substr($url,0,4) != 'http' )
            {
                throw new \LogicException('请输入正确的URL');
            }

            $baseUrl = explode('.',parse_url(kernel::base_url(1),PHP_URL_HOST));
            krsort($baseUrl);

            $checkUrl = explode('.',parse_url($url, PHP_URL_HOST));
            krsort($checkUrl);

            if( $baseUrl[0] != $checkUrl[0] || (isset($baseUrl[1]) && $baseUrl[1] != $checkUrl[1]) )
            {
                throw new \LogicException(app::get('sysdecorate')->_('导航关联链接只能为站内链接'));
            }
        }

        return true;
    }

    /**
     * @desc 添加单图展示挂件
     */
    public function save($params)
    {
        $baseUrl = kernel::base_url(1);
        foreach( $params['list'] as $key=>$value )
        {
            $this->__checkUrl($value['navlink']);

            $data['params'][$key] = [
                'name'   => $value['name'],
                'type'   => $value['type'],
                'item_ids' => $value['item_ids'],
                'navlink' => $value['navlink'],
                'promotion_id' => $value['promotion_id'],
                'promotion_type' => $value['promotion_type'],
            ];
        }

        if( $params['widgets_id'] )
        {
            $data['widgets_id']   = $params['widgets_id'];
        }

        $data['order_sort']   = $params['order_sort'];
        $data['shop_id']      = $params['shop_id'];
        $data['widgets_type'] = 'nav';//单图展示

        return kernel::single('sysdecorate_new_widgets')->setConfig($params['page_name'], $params['platform'], $data);
    }
}
