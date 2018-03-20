<?php
/**
 * 分类api数据
 */
class syscategory_data_virtualcat {

    public function __construct($platform)
    {
        $this->objMdlCat = app::get('syscategory')->model('virtualcat');
        $this->platform = $platform;
    }

    public function toSave($postdata){

        $result = $this->objMdlCat->save($postdata);
        event::fire('virtualcat.save',[$this->platform]);
        if(!$result){
            throw new Exception(app::get('syscategory')->_('保存失败'));
        }
        return $result;
    }

    /**
     * 删除分类
     * @param  int $catId 分类id
     * @return bool
     */
    public function toRemove($catId)
    {
        $filter = [
            'virtual_parent_id' => intval($catId),
            'platform' => $this->platform,
        ];
        $aCats = $this->objMdlCat->getRow('virtual_cat_id', $filter);
        if($aCats)
        {
            $msg = '删除失败：本分类下面还有子分类';
            throw new \LogicException($msg);
            return false;
        }

        $parentRow = $this->objMdlCat->getRow('virtual_parent_id', ['virtual_cat_id' => intval($catId),'platform' => $this->platform]);

        $db = app::get('syscategory')->database();
        $db->beginTransaction();
        try
        {
            $result = $this->objMdlCat->database()->delete('syscategory_virtualcat', ['virtual_cat_id' => intval($catId),'platform' => $this->platform], [\PDO::PARAM_INT]);
            if(!$result) throw new \LogicException("删除类目失败");

            if($parentRow['virtual_parent_id'])
            {
                $result = $this->objMdlCat->database()->executeUpdate('UPDATE syscategory_virtualcat SET child_count = child_count-1 WHERE virtual_cat_id=? and platform=?', [$parentRow['virtual_parent_id'],$this->platform], [\PDO::PARAM_INT]);
                if(!$result) throw new \LogicException("更新父级下的子级数量失败");
            }
            
            $db->commit();
            event::fire('virtualcat.save',[$this->platform]);
        }
        catch(\LogicException $e)
        {
            $db->rollback();
            throw new \LogicException($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 更新分类排序
     * @param  array $sortData 分类排序数组 array('order_sort'=>array($virtual_cat_id=>$sort_number,......))
     * @param  string $msg 返回错误信息
     * @return bool
     */
    public function updateSort($sortData)
    {
        foreach( $sortData as $k => $v )
        {
            $this->objMdlCat->update( array('order_sort'=>($v==='' ? null : $v)), array('virtual_cat_id'=>$k,'platform'=>$this->platform));
            event::fire('virtualcat.save',[$this->platform]);
        }
        return true;
    }

    /**
     * 获取分类的全部数据
     * @param string fields 数据结构
     *
     * @return list
     */
    public function getAll()
    {
        return $this->objMdlCat->getCatList(true,$this->platform);
    }

    /**
     * 获取分类的树形结构数据
     * @param string fields 数据结构
     *
     * @return tree
     */
    public function getTree()
    {
        $tree = unserialize(redis::scene('system')->get($this->platform.'_virtualcat'));
        if($tree){
            $Tree = $tree;
        }else{
            $Tree = $this->makeTree();
        }
        return $Tree;
    }

    // 获取分类列表并生成分类树数组
    private function getAndGenTree()
    {
        set_time_limit(2000);
        $data = app::get('syscategory')->model('virtualcat')->getList('virtual_cat_id,virtual_parent_id,virtual_cat_name,virtual_cat_logo,url,cat_path,level,is_leaf,child_count,order_sort,platform',array('platform'=>$this->platform),0,-1,'order_sort ASC, virtual_cat_id DESC');
        $list = $this->genTree($data);

        return $list;
    }

    // 生成分类三维数组
    private function genTree(&$data, $pId)
    {
        $tree = '';
        foreach($data as $k => $v)
        {
           if($v['virtual_parent_id'] == $pId)
           {
                if($v['level']==1){
                    $v['lv2'] = $this->genTree($data, $v['virtual_cat_id']);
                }
                if($v['level']==2){
                    $v['lv3'] = $this->genTree($data, $v['virtual_cat_id']);
                }
                $tree[] = $v;
           }
        }
        return $tree;
    }

    public function makeTree()
    {
        $tree = $this->getAndGenTree();
        redis::scene('system')->set($this->platform.'_virtualcat',serialize($tree));
        return $tree;
    }

    /**
     *
     *maplist
     */
    public function maplist(){
        $rows = $this->getAndGenTree();
        $map = $this->parse_listmaps($rows);
        return $map;
    }

    private function parse_listmaps($rows)
    {
        $data = array();
        foreach((array)$rows AS $k=>$v)
        {
            $lv2 = $v['lv2'];
            if(isset($v['lv2']))  unset($v['lv2']);
            $data[] = $v;
            if($lv2){
                foreach ($lv2 as $key => $value) {
                    $lv3 = $value['lv3'];
                    if(isset($value['lv3']))  unset($value['lv3']);
                    $data[] = $value;
                    if($lv3){
                        $data = array_merge($data, $this->parse_listmaps($lv3));
                    }
                }
            }
        }
        return $data;
    }
}


