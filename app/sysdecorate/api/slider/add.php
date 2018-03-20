<?php
/**
 * -- sysdecorate.slider.add
 * -- 添加轮播图挂件
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysdecorate_api_slider_add {

    public $apiDescription = '添加轮播图挂件';

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
            'list'       => ['type'=>'jsonArray', 'valid'=>'required', 'desc'=>'轮播图列表', 'msg'=>'请选择至少一张轮播图！', 'params'=>array(
                'imgurl'     => ['type'=>'string', 'valid'=>'required', 'desc'=>'图片地址', 'msg'=>'请选择图片！'],
                'imglink'    => ['type'=>'string', 'valid'=>'', 'desc'=>'图片关联链接', 'msg'=>'请填写图片链接'],
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
                throw new \LogicException(app::get('sysdecorate')->_('多图图片关联链接只能为站内链接'));
            }
        }

        return true;
    }

    /**
     * @desc 添加单图展示挂件
     */
    public function save($params)
    {
        foreach( $params['list'] as $key=>$value )
        {
            $this->__checkUrl($value['imglink']);

            $data['params'][$key] = [
                'imgurl'  => $value['imgurl'],
                'imglink' => $value['imglink'],
            ];
        }

        if( $params['widgets_id'] )
        {
            $data['widgets_id']   = $params['widgets_id'];
        }

        $data['order_sort']   = $params['order_sort'];
        $data['shop_id']      = $params['shop_id'];
        $data['widgets_type'] = 'slider';//轮播图展示

        return kernel::single('sysdecorate_new_widgets')->setConfig($params['page_name'], $params['platform'], $data);
    }
}
