<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topm_ctl_content extends topm_controller {

    public function __construct(&$app)
    {
        parent::__construct();
        $this->setlayoutflag('content_info');
    }

    //获取文章节点树
    public function nodeList()
    {
        $nodeList = $this->__getCommonInfo();

        $pagedata['nodeList'] = $nodeList;
        $pagedata['title'] = "文章目录";
        //echo '<pre>';print_r($pagedata);exit();
        return $this->page('topm/content/nodelist.html', $pagedata);
    }
    //获取文章列表
    public function contentList()
    {
        $filter = input::get();
        $pageSize = 20;
        if(!$filter['pages'])
        {
            $filter['pages'] = 1;
        }
        $params = array(
            'node_id'   => $filter['node_id'],
            'page_no'   => $pageSize*($filter['pages']-1),
            'page_size' => $pageSize,
            'fields'    =>'article_id,title,node_id,modified',
            'platform'  =>'wap',
        );
        $contentData = app::get('topm')->rpcCall('syscontent.content.get.list',$params);

        $count = $contentData['articlecount'];
        $contentList = $contentData['articleList'];
        $nodeInfo = $contentData['nodeInfo'];
        //处理翻页数据
        $current = $filter['pages'] ? $filter['pages'] : 1;
        $filter['pages'] = time();
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topm_ctl_content@contentList',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );
        $pagedata['contentList']= $contentList;
        $pagedata['count'] = $count;
        $pagedata['nodeInfo'] = $nodeInfo;
        $pagedata['title'] = "文章列表";
        return $this->page('topm/content/contentlist.html', $pagedata);
    }
    //获取文章详细信息
    public function getContentInfo()
    {
        $post = input::get();
        //获取文章节点树
        $nodeList = $this->__getCommonInfo();
        $pagedata['nodeList'] = $nodeList;

        $params = array(
            'article_id' => $post['article_id'],
            'fields' =>'article_id,title,modified,content,node_id,tmpl_path',
        );

        $contentInfo = app::get('topm')->rpcCall('syscontent.content.get.info',$params);
        $pagedata['contentInfo'] = $contentInfo;
        $pagedata['title'] = "文章详情";
        //设置文章详情seo
        //获取文章栏目层级关系
        $arr = $this->__getNodenav($nodeList, $contentInfo['node_id']);
        $nodeNames = implode('_', $arr);
        //文章描述，截取正文200个长度
        $desc = mb_substr(strip_tags($contentInfo['content']), 0, 200, 'UTF-8');
        $seoData = array(
                'content_title' => $contentInfo['title'],
                'content_cat' =>$nodeNames,
                'content_desc' => $desc,
        );
        seo::set('topc.content.detail',$seoData);
        
        $this->setLayout($contentInfo['tmpl_path']);
        return $this->page('topm/content/contentinfo.html', $pagedata);
    }
    
    /**
     *  获取指定栏目的名称和父名称
     *  @param array $nodeList
     *  @param int $nodeId
     *  @return array
     * */
    private function __getNodenav($nodeList, $nodeId)
    {
        static $nav = array();
        static $arr = array();
        foreach ($nodeList as $value)
        {
            $nav[$value['node_id']] = $value['node_name'];
            if($value['node_id'] == $nodeId)
            {
                if($value['node_path'])
                {
                    $path  = $value['node_path'];
                    $path = explode(',', $path);
                    foreach ($path as $v)
                    {
                        $arr[] = $nav[$v];
                    }
                }else
                {
                    $arr[] = $value['node_name'];
                }
            }else
            {
                $this->__getNodenav($value['children'], $nodeId);
            }
    
        }
    
        return array_reverse($arr);
    }
    
    //获取文章节点树
    private function __getCommonInfo()
    {
        $params['fields'] = 'node_id,node_name,parent_id,node_depth,node_path';
        $params['parent_id'] = 0 ;
        $params['orderBy'] = 'order_sort ASC';
        $nodeList = app::get('topm')->rpcCall('syscontent.node.get.list',$params);
        return $nodeList;
    }

}
