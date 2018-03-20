<?php
/**
 * topapi
 *
 * -- content.node.list
 * -- 获取文章节点列表
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topapi_api_v1_content_getNodeList implements topapi_interface_api{

	/**
     * 接口作用说明
     */
    public $apiDescription = '获取目录列表';

    public function setParams()
    {
        return [
            'parent_id' => ['type'=>'int',  'valid'=>'','example'=>'1',      'desc'=>'父级分类id',      'msg'=>'父级分类id'],
            'fields' => ['type'=>'field_list',  'valid'=>'',                   'example'=>'title,modified,content,node_id','desc'=>'要获取的字段集。多个字段用“,”分隔','msg'=>''],
            'orderBy' =>['type'=>'string', 'valid'=>'', 'example'=>'','desc'=>'排序',   'msg'=>''],
        ];
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $nodelist = app::get('topapi')->rpcCall('syscontent.node.get.list',$params);

        $result = [];
        $i = 0;
        foreach ($nodelist as $value) {
            $result['list'][$i] = $value;
            $i++;
        }

        return $result;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"list":[{"node_id":17,"parent_id":0,"node_name":"ONex产品介绍","children":[{"node_id":25,"parent_id":17,"node_depth":2,"node_name":" 朋克","node_path":"17,25","has_children":0,"ifpub":0,"order_sort":8,"modified":1453950923},{"node_id":24,"parent_id":17,"node_depth":2,"node_name":"电商中台","node_path":"17,24","has_children":0,"ifpub":0,"order_sort":7,"modified":1453950910},{"node_id":23,"parent_id":17,"node_depth":2,"node_name":"OMS订单管理","node_path":"17,23","has_children":0,"ifpub":0,"order_sort":6,"modified":1453950884},{"node_id":22,"parent_id":17,"node_depth":2,"node_name":"CRM会员营销","node_path":"17,22","has_children":0,"ifpub":0,"order_sort":5,"modified":1453950870},{"node_id":21,"parent_id":17,"node_depth":2,"node_name":"ECstore","node_path":"17,21","has_children":0,"ifpub":0,"order_sort":4,"modified":1453950844},{"node_id":20,"parent_id":17,"node_depth":2,"node_name":"智慧门店","node_path":"17,20","has_children":0,"ifpub":0,"order_sort":3,"modified":1453950110},{"node_id":19,"parent_id":17,"node_depth":2,"node_name":"在线零售","node_path":"17,19","has_children":0,"ifpub":0,"order_sort":2,"modified":1453950096},{"node_id":18,"parent_id":17,"node_depth":2,"node_name":"多用户商城","node_path":"17,18","has_children":0,"ifpub":0,"order_sort":1,"modified":1453950084}]},{"node_id":13,"parent_id":0,"node_name":"常见问题","children":[{"node_id":16,"parent_id":13,"node_depth":2,"node_name":"其他","node_path":"13,16","has_children":0,"ifpub":0,"order_sort":3,"modified":1453949843},{"node_id":15,"parent_id":13,"node_depth":2,"node_name":"安全管理","node_path":"13,15","has_children":0,"ifpub":0,"order_sort":2,"modified":1453949824},{"node_id":14,"parent_id":13,"node_depth":2,"node_name":"交易规则","node_path":"13,14","has_children":0,"ifpub":0,"order_sort":1,"modified":1453949816}]},{"node_id":8,"parent_id":0,"node_name":"公司简介","children":[{"node_id":12,"parent_id":8,"node_depth":2,"node_name":"成为合作伙伴","node_path":"8,","has_children":0,"ifpub":0,"order_sort":4,"modified":1453958856},{"node_id":11,"parent_id":8,"node_depth":2,"node_name":"招聘信息","node_path":"8,11","has_children":0,"ifpub":0,"order_sort":3,"modified":1453949753},{"node_id":10,"parent_id":8,"node_depth":2,"node_name":"联系我们","node_path":"8,10","has_children":0,"ifpub":0,"order_sort":2,"modified":1453949735},{"node_id":9,"parent_id":8,"node_depth":2,"node_name":"关于我们","node_path":"8,9","has_children":0,"ifpub":0,"order_sort":1,"modified":1453949726}]},{"node_id":3,"parent_id":0,"node_name":"帮助中心","children":[{"node_id":7,"parent_id":3,"node_depth":2,"node_name":"购物条款","node_path":"3,7","has_children":0,"ifpub":0,"order_sort":4,"modified":1453949704},{"node_id":6,"parent_id":3,"node_depth":2,"node_name":"支付/配送方式","node_path":"3,6","has_children":0,"ifpub":0,"order_sort":3,"modified":1453949694},{"node_id":5,"parent_id":3,"node_depth":2,"node_name":"购物指南","node_path":"3,5","has_children":0,"ifpub":0,"order_sort":2,"modified":1453949673},{"node_id":4,"parent_id":3,"node_depth":2,"node_name":"新手上路","node_path":"3,4","has_children":0,"ifpub":0,"order_sort":1,"modified":1453949636}]},{"node_id":1,"parent_id":0,"node_name":"推广文章","children":[{"node_id":2,"parent_id":1,"node_depth":2,"node_name":"说说","node_path":"1,2","has_children":0,"ifpub":0,"order_sort":1,"modified":1453874589}]}]}}';
    }
}
