<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class syscategory_mdl_virtualcat extends dbeav_model
{
    public $defaultOrder = array('order_sort',' asc',',virtual_cat_id',' DESC');

    /**
     * 构造方法
     * @param object model相应app的对象
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * 保存的方法
     * @param  mixed  $aData      保存的数据内容
     * @param  boolean  $mustUpdate 是否必须更新
     * @param  boolean $mustInsert 是否必须插入
     * @return boolea              是否保存成功
     */
    public function save(&$aData, $mustUpdate = null, $mustInsert = false)
    {
        if($aData['virtual_cat_id'])
        {
            $oldData = $this->getRow('virtual_cat_id, virtual_parent_id, level',array('virtual_cat_id'=>$aData['virtual_cat_id'],'platform'=>$aData['platform']) );
        }

        if(!$oldData)
        {
            $flag = 'add'; // 添加分类
        }
        else
        {
            $flag = 'edit'; // 编辑分类
        }

        if($flag == 'edit')
        {
            // 如果编辑后的virtual_parent_id与原来分类的virtual_parent_id不一直则报错
            if($aData['virtual_parent_id'] && ($oldData['virtual_parent_id'] != $aData['virtual_parent_id']))
            {
                $msg = '您不能修改分类的上级分类virtual_parent_id';
                throw new \LogicException($msg);
                return false;
            }
        }

        if($flag == 'add')
        {
            // 添加子节点后更新父节点的子节点数量字段
            if($aData['virtual_parent_id'] != 0)
            {
                $row = $this->getRow('child_count, level', array('virtual_cat_id'=>$aData['virtual_parent_id']) );
                // 如果节点已经是三级节点了，则不允许添加子节点了
                if($row['level'] == '3')
                {
                    $msg = '最多只能添加三级分类';
                    throw new \LogicException($msg);
                    return false;
                }
                // 如果父节点是二级分类，则此节点则为叶子节点
                if($row['level'] == '2')
                {
                    $aData['is_leaf'] = 1;
                }
                $parentData['child_count'] = $row['child_count']+1;
                $parentData['virtual_cat_id'] = $aData['virtual_parent_id'];
                parent::save($parentData);
            }
        }
        if($aData['virtual_parent_id'])
        {
            $aData['cat_path'] = $this->genCatPath($aData['virtual_parent_id']); // 分类路径
            $aData['level'] = substr_count($aData['cat_path'],','); // 分类层级
        }
        
        return parent::save($aData);
    }

    /**
     * 生成分类节点的路径
     * @param  int $virtual_parent_id 父节点分类id
     * @return string 分类节点路径
     */
    public function genCatPath($virtual_parent_id)
    {
        if($virtual_parent_id == 0)
        {
            return ',';
        }
        $cat_sdf = $this->getRow('virtual_cat_id, cat_path', array('virtual_cat_id'=>$virtual_parent_id));

        return $cat_sdf['cat_path'].$cat_sdf['virtual_cat_id'].",";
    }

    /**
     * 得到整个分类树形结构
     * @param null
     * @return mixed 返回的数据
     */
    public function getTree($platform)
    {
        $fields = 'virtual_cat_name,virtual_cat_id,virtual_parent_id,order_sort,level,virtual_cat_id,cat_path,is_leaf,child_count,platform';
        return $this->getList($fields, array('platform'=>$platform), 0, -1, 'order_sort ASC, virtual_cat_id DESC');
    }

    public function getMapTree($ss=0, $str='└',$platform)
    {
        $var_ss = $ss;
        $var_str = $str;
        if(isset($this->catMapTree[$var_ss][$var_str]))
        {
            return $this->catMapTree[$var_ss][$var_str];
        }
        $retCat = $this->map($this->getTree($platform), $ss, $str, $no, $num);
        $this->catMapTree[$var_ss][$var_str] = $retCat;
        global $step, $cat;
        $step = '';
        $cat = array();
        return $retCat;
    }

    public function map($data, $sID=0, $preStr='', &$cat_cuttent, &$step)
    {
        set_time_limit(2000);
        $step++;
        if($data)
        {
            $tmpCat = array();
            foreach($data as $i => $value)
            {
                $count = substr_count( $data[$i]['cat_path'],',' );
                $id = $data[$i]['virtual_cat_id'];
                $cls = ($data[$i]['child_count']?'true':'false');

                $tmpCat[$value['virtual_parent_id']][] = array(
                    'virtual_cat_id' => $data[$i]['virtual_cat_id'],
                    'virtual_parent_id' => $data[$i]['virtual_parent_id'],
                    'virtual_cat_name' => $data[$i]['virtual_cat_name'],
                    'virtual_cat_logo' => $data[$i]['virtual_cat_logo'],
                    'cat_path' => $data[$i]['cat_path'],
                    'level' => $data[$i]['level'],
                    'is_leaf' => $data[$i]['is_leaf'],
                    'child_count' => $data[$i]['child_count'],
                    'platform' => $data[$i]['platform'],
                    'order_sort' => $data[$i]['order_sort'],
                    'type' => $data[$i]['type'],
                    'step' => $count?$count:1,
                    'cls' => $cls,
                );
            }
            $this->_map($cat_cuttent, $tmpCat, 0);
        }
        $step--;
        return $cat_cuttent;
    }

    public function _map(&$cat_cuttent, $data, $key)
    {
        if(is_array($data[$key]))
        {
            foreach($data[$key] as $k => $v)
            {
                $cat_cuttent[] = $v;
                if($data[$v['virtual_cat_id']])
                {
                    $this->_map($cat_cuttent, $data, $v['virtual_cat_id']);
                }
            }
        }
    }

    public function getCatList($show_stable=false, $platform)
    {
        return $this->getMapTree(0, '', $platform);
    }

    public function modifier_cat_service_rates($cols)
    {
        foreach ($cols as $key => $val) {
            $cols[$key] = $cols[$key].'%';
        }
    }

}
