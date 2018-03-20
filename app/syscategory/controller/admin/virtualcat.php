<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class syscategory_ctl_admin_virtualcat extends desktop_controller {

    public $workground = 'syscategory.workground.category';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->platform = 'pc';
        $this->objMdl = app::get('syscategory')->model('virtualcat');
        $this->objLibVirtualcat = kernel::single('syscategory_data_virtualcat',$this->platform);
    }

    public function index()
    {
        $tree = $this->objMdl->getCatList(false,$this->platform);

        $pagedata['tree_number'] = count($tree);
        if($tree)
        {
            foreach($tree as $k=>$v)
            {
                $tree[$k]['link'] = array('virtual_cat_id' => array(
                    'v' => $v['virtual_cat_id'],
                    't' => app::get('syscategory')->_('商品类别').app::get('syscategory')->_('是').$v['virtual_cat_name']
                ));
            }
        }

        $pagedata['tree'] = $tree;
        return $this->page('syscategory/admin/virtualcat/map.html', $pagedata);
    }

    /**
     * 添加分类
     * @param integer $nCatId 分类id
     */
    public function add($nCatId = 0)
    {
        $catList[0] = array('virtual_cat_id'=>0, 'virtual_cat_name'=>app::get('syscategory')->_('----无----'), 'step'=>1);

        $virtualCatInfo = $this->objMdl->getRow('cat_path, level, virtual_cat_name, virtual_cat_logo, virtual_cat_id, virtual_parent_id,platform', array('virtual_cat_id'=>$nCatId,'platform'=>$this->platform));
        if($virtualCatInfo['level']=='1')
        {
            $catList[1] = array('virtual_cat_id'=>$virtualCatInfo['virtual_cat_id'],'virtual_cat_name'=>$virtualCatInfo['virtual_cat_name'], 'step'=>'1');
        }
        if($virtualCatInfo['level']=='2')
        {
            $pagedata['level'] = 3;
            $level1CatInfo = $this->objMdl->getRow('cat_path, level, virtual_cat_name, virtual_cat_logo, virtual_cat_id, virtual_parent_id,platform', array('virtual_cat_id'=>$virtualCatInfo['virtual_parent_id'],'platform'=>$this->platform));
            $catList[1] = array('virtual_cat_id'=>$level1CatInfo['virtual_cat_id'], 'virtual_cat_name'=>$level1CatInfo['virtual_cat_name'], 'step'=>1);
            $catList[2] = array('virtual_cat_id'=>$virtualCatInfo['virtual_cat_id'], 'virtual_cat_name'=>$virtualCatInfo['virtual_cat_name'], 'step'=>2);
        }

        $pagedata['virtual_parent_id'] = $nCatId;
        if(!$nCatId)
        {
            $pagedata['level'] = 1;
        }
        $pagedata['level'] = $virtualCatInfo['level']+1;
        $pagedata['catList'] = $catList;
        
        $categorylist = app::get('syscategory')->rpcCall('category.cat.get.list');

        foreach( $categorylist as $row )
        {
            if( $row['level'] == 1 )
            {
                if($row['lv2']) unset($row['lv2']);
                $selectNode[] = $row;
            }
        }
        $pagedata['selectNode'] = $selectNode;
        $pagedata['filter']['selector'] ='filter';

        return view::make('syscategory/admin/virtualcat/info.html', $pagedata);
    }

    /**
     * 编辑分类
     * @param  integer  $nCatId  分类id
     * @param  integer $is_leaf 是否叶子节点
     * @return
     */
    public function edit($nCatId, $is_leaf=0)
    {
        $pagedata['is_leaf'] = input::get('is_leaf');
        $virtualCatInfo = $this->objMdl->getRow('cat_path, level, virtual_cat_name, virtual_cat_logo, virtual_cat_id, virtual_parent_id, order_sort, filter,virtual_cat_template', array('virtual_cat_id'=>$nCatId,'platform'=>$this->platform));

        // 组织上级分类数据，1级分类显示无，2级分类显示1级分类,3级分类则显示对应的2级和1级分类
        $catList[0] = array('virtual_cat_id'=>0, 'virtual_cat_name'=>app::get('syscategory')->_('----无----'), 'step'=>1);
        if($virtualCatInfo['level']=='2')
        {
            $level1CatInfo = $this->objMdl->getRow('cat_path, level, virtual_cat_name, virtual_cat_id, virtual_parent_id, order_sort,virtual_cat_template', array('virtual_cat_id'=>$virtualCatInfo['virtual_parent_id'],'platform'=>$this->platform));
            $catList[1] = array('virtual_cat_id'=>$level1CatInfo['virtual_cat_id'], 'virtual_cat_name'=>$level1CatInfo['virtual_cat_name'], 'step'=>1);
        }
        if($virtualCatInfo['level']=='3')
        {
            $level2CatInfo = $this->objMdl->getRow('cat_path, level, virtual_cat_name, virtual_cat_id, virtual_parent_id,  order_sort', array('virtual_cat_id'=>$virtualCatInfo['virtual_parent_id'],'platform'=>$this->platform));
            $level1CatInfo = $this->objMdl->getRow('cat_path, level, virtual_cat_name, virtual_cat_id, virtual_parent_id, order_sort', array('virtual_cat_id'=>$level2CatInfo['virtual_parent_id'],'platform'=>$this->platform));
            $catList[1] = array('virtual_cat_id'=>$level1CatInfo['virtual_cat_id'], 'virtual_cat_name'=>$level1CatInfo['virtual_cat_name'], 'step'=>1);
            $catList[2] = array('virtual_cat_id'=>$level2CatInfo['virtual_cat_id'], 'virtual_cat_name'=>$level2CatInfo['virtual_cat_name'], 'step'=>2);
        }
        $pagedata['catList'] = $catList;

        $pagedata['virtual_cat_id'] = $virtualCatInfo['virtual_cat_id'];
        $pagedata['virtual_cat_name'] = $virtualCatInfo['virtual_cat_name'];
        $pagedata['virtual_parent_id'] = $virtualCatInfo['virtual_parent_id'];
        $pagedata['order_sort'] = $virtualCatInfo['order_sort'];
        $pagedata['level'] = $virtualCatInfo['level'];
        $pagedata['virtual_cat_logo'] = $virtualCatInfo['virtual_cat_logo'];
        $pagedata['virtual_cat_template'] = $virtualCatInfo['virtual_cat_template'];
        $pagedata['filter'] = unserialize($virtualCatInfo['filter']);
        if($pagedata['filter']['cat_id']){
            $pagedata['catData'] = app::get('syscategory')->rpcCall('category.cat.get.data',array('cat_id'=>$pagedata['filter']['cat_id']));
            $pagedata['catData'] = json_encode($pagedata['catData']);
        }
        $categorylist = app::get('syscategory')->rpcCall('category.cat.get.list');

        foreach( $categorylist as $row )
        {
            if( $row['level'] == 1 )
            {
                if($row['lv2']) unset($row['lv2']);
                $selectNode[] = $row;
            }
        }

       $pagedata['selectNode'] = $selectNode;
        return view::make('syscategory/admin/virtualcat/info.html', $pagedata);
    }

    /**
     * 保存节点信息
     * @return
     */
    public function save()
    {
        $postData = $_POST;
        if($postData['is_leaf'])
        {
            $this->begin('?app=syscategory&ctl=admin_virtualcat&act=leaf&virtual_parent_id='.$postData['virtual_parent_id']);
        }
        else
        {
            $this->begin('?app=syscategory&ctl=admin_virtualcat&act=index');
        }

        $postData = $this->__preFilter($_POST);
        $postData['platform'] = $this->platform;
        $postData['filter'] = serialize($postData['filter']);

        $virtualCatInfo = $this->objMdl->getRow('virtual_cat_id', array('virtual_cat_name'=>$postData['virtual_cat_name'],'virtual_parent_id'=>$postData['virtual_parent_id'],'platform'=>$this->platform));
        if( $virtualCatInfo && intval($postData['virtual_cat_id']) != $virtualCatInfo['virtual_cat_id'] )
        {
            $this->end(false, app::get('syscategory')->_('同级分类下名称不能重复!'));
        }
        else
        {
            try{

                $result = $this->objLibVirtualcat->toSave($postData);

                if(!$result){
                    throw new RuntimeException(app::get('syscategory')->_('保存失败'));
                }

                $this->adminlog("添加、编辑商品类目[ID:{$postData['virtual_cat_name']}]", 1);
                $this->end(true, app::get('syscategory')->_('保存成功'));
            } catch(Exception $e) {
                $this->adminlog("添加、编辑商品类目[ID:{$postData['virtual_cat_name']}]", 0);
                $this->end(false, app::get('syscategory')->_($e->getMessage()));
            }
        }

    }
    
    /**
     * 删除分类(一级和二级)
     * @param  int $nCatId 分类id
     * @return
     */
    public function toRemove()
    {
        $nCatId = input::get('nCatId');
        if($_GET['from_finder'])
        {
            $this->begin('?app=syscategory&ctl=admin_virtualcat&act=leaf&virtual_parent_id='.input::get('virtual_parent_id'));
        }
        else
        {
            $this->begin('?app=syscategory&ctl=admin_virtualcat&act=index');
        }
        try
        {
            $flag = $this->objLibVirtualcat->toRemove(intval($nCatId));
            $this->adminlog("删除商品类目[ID:{$nCatId}]", 1);

        }
        catch(\LogicException $e)
        {
            $this->adminlog("删除商品类目[ID:{$nCatId}]", 0);
            $msg = $e->getMessage();
            $this->end(false,$msg);
        }

        $virtualCatInfo = $this->objMdl->getRow('virtual_cat_name', array('virtual_cat_id'=>intval($nCatId),'platform'=>$this->platform));

        $this->end(true, $virtualCatInfo['virtual_cat_name'].app::get('syscategory')->_('已删除'));
    }

    /**
     * 主要用用于更新菜单排序
     * @return
     */
    public function updateSort()
    {
        $postData = $_POST['order_sort'];
        $this->begin('?app=syscategory&ctl=admin_virtualcat&act=index');
        $this->objLibVirtualcat->updateSort($postData);
        $this->adminlog("更新商品类目排序", 1);
        $this->end(true, app::get('syscategory')->_('更新排序操作完成'));
    }

    private function __preFilter($postData)
    {
        if(empty($postData['filter']['min_price']))
        {
            unset($postData['filter']['min_price']);
        }
        if(!trim($postData['filter']['search_keywords']))
        {
            unset($postData['filter']['search_keywords']);
        }

        if(empty($postData['filter']['max_price']))
        {
            unset($postData['filter']['max_price']);
        }
        if(!$postData['filter']['brand_id'])
        {
            unset($postData['filter']['brand_id']);
        }
        if($postData['filter']['cat_id'] == '-1'){
            unset($postData['filter']['cat_id']);
        }

        if($postData['selector'] == 'filter'){
            unset($postData['customUrl']);
            $postData['url'] ='';
        }elseif ($postData['selector'] == 'custom') {
            unset($postData['filter']);
            $postData['filter']['customUrl'] = $postData['customUrl'];
            $postData['url'] = $postData['customUrl'];
        }

        if( $postData['order_sort'] === '' )
        {
            $postData['order_sort'] = 0;
        }
        if(!trim($postData['virtual_cat_name']))
        {
            $this->end(false, app::get('syscategory')->_('分类名称不能为空!'));
        }

        if( mb_strlen($postData['virtual_cat_name'])>100)
        {
            $this->end(false, app::get('syscategory')->_('分类名称不能超过100个字符!'));
        }

        if($postData['selector']){
            $postData['filter']['selector'] = $postData['selector'];
            unset($postData['selector']);
        }

        if(isset($postData['filter']) && count($postData['filter'])<2){
            $this->end(false, app::get('syscategory')->_('至少添加一项筛选规则！'));
        }

        return $postData;
    }
}
