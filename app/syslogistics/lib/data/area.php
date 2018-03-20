<?php
/**
 *
 */
class syslogistics_data_area {

    public function __construct()
    {
        $this->redis = redis::scene('syslogistics');

        if( !$this->redis->get('cacheAreaData') )
        {
            $this->__preKeyValue();

            foreach( $this->areaIdPath as $pid=>$row )
            {
                $this->redis->hset('areaIdPath', $pid, json_encode($row));
            }

            $this->redis->set('cacheAreaData', true);
        }
    }

    /**
     * 返回地区ID和地区名
     */
    private function __preKeyValue($contents)
    {
        if( !$contents ) $contents = $this->__getAreaFileContents();
        foreach( $contents as $key=>$value )
        {
            if( !$value['disabled'] )
            {
                $this->areaIdPath[$value['parentId']][] = $value['id'];
            }

            $redisSaveData = [
                'value'    => $value['value'],
                'parentId' => $value['parentId'],
                'disabled' => $value['disabled'],
            ];

            $this->redis->hset('areaKvdata', $value['id'], json_encode($redisSaveData));

            if( !empty($value['children']) )
            {
                $this->__preKeyValue($value['children']);
            }
        }
        return true;
    }

    private function __getAreaFileContents()
    {
        if( $this->areaFileContents ) return $this->areaFileContents;
        $this->areaFileContents = json_decode(redis::scene('syslogistics')->get('areaFileContents'), true);
        if ( !$this->areaFileContents )
        {
            $file = app::get('ectools')->res_dir.'/scripts/region.json';
            if( !is_file($file) )
            {
                $this->initFileContents();
                $this->resetFile();
            }
            else
            {
                $this->areaFileContents = json_decode(file_get_contents($file),true);
                $this->__setKvArea();
            }
        }

        return $this->areaFileContents;
    }

    public function initFileContents()
    {
        $staticsHostUrl = kernel::get_app_statics_host_url();
        $fileDir = $staticsHostUrl.'/ectools/statics/scripts/area.json';
        $this->areaFileContents = json_decode(file_get_contents($fileDir),true);
        $this->__setKvArea();
    }

    private function __setKvArea()
    {
        $this->redis->set('areaFileContents', json_encode($this->areaFileContents));
        $this->redis->set('cacheAreaData', false);
        return true;
    }

    public function resetFile()
    {
        $this->__getAreaFileContents();
        $this->__editAreaData($this->areaFileContents,'reset');
        sort($this->areaFileContents);
        $file = app::get('ectools')->res_dir.'/scripts/region.json';
        if( !file_put_contents($file, json_encode($this->areaFileContents)) )
        {
            throw new LogicException('文件写入失败：'.$file.'请检查是否有写权限');
        }
    }

    public function editArea($type, $areaId, $areaName)
    {
        $this->__getAreaFileContents();

        if( $type == 'add' && $areaId || $type != 'add' )
        {
            $this->__editAreaData($this->areaFileContents, $type, $areaId, $areaName);
        }
        else
        {
            $this->areaFileContents[] = ['id'=>$this->__genAreaId(),'value'=>$areaName,'parentId'=>$areaId];
        }

        if( $type != 'reset' )
        {
            $this->__setKvArea();
        }
    }

    //如果并发的时候可能会有问题，导致ID一致，但是在添加地区的时候，正常情况下添加地区不会并发
    private function __genAreaId($parentId)
    {
        if (!($areaIdIndex = redis::scene('syslogistics')->get('areaIdIndex')))
        {
            $areaIdIndex = $id = 900000;
        }
        else
        {
            if( is_array($areaIdIndex) )
            {
                $index =  max($areaIdIndex);
                unset($areaIdIndex);
                $areaIdIndex = $index;
            }

            $areaIdIndex = $id = $areaIdIndex + 1;
        }

        redis::scene('syslogistics')->set('areaIdIndex', $areaIdIndex);
        return $id.rand(999);
    }

    private function __editAreaData(&$areaFileContents, $type='reset', $areaId, $areaName)
    {
        foreach( $areaFileContents as $key=>$value )
        {
            switch($type)
            {
                case 'remove':
                    if( $value['id'] == $areaId )
                    {
                        $areaFileContents[$key]['disabled'] = true;
                    }
                    break;
                case 'update':
                    if( $value['id'] == $areaId )
                    {
                        $areaFileContents[$key]['value'] = $areaName;
                        break;
                    }
                case 'add':
                    if( $value['id'] == $areaId )
                    {
                        $id = $this->__genAreaId();
                        $areaFileContents[$key]['children'][] = ['value'=>$areaName,'id'=>$id,'parentId'=>$areaId];
                        break;
                    }
                default :
                    if(isset($value['disabled']) && $value['disabled'] == 1)
                    {
                        unset($areaFileContents[$key]);
                        unset($value);
                    }
            }

            if(isset($value['children']) && is_array($value['children']))
            {
                $this->__editAreaData($areaFileContents[$key]['children'], $type, $areaId, $areaName);
            }
        }

        return true;
    }

    /**
     * 返回所有地区内容
     *
     *array(
     *  18 =>
     *    array (
     *      'id' => '430000',
     *      'value' => '湖南省',
     *      'parentId' => '1',
     *      'children' =>
     *      array (
     *        0 =>
     *        array (
     *          'id' => '430100',
     *          'value' => '长沙市',
     *          'parentId' => '430000',
     *          'children' =>
     *          array (
     *            0 =>
     *            array (
     *              'id' => '430102',
     *              'value' => '芙蓉区',
     *              'parentId' => '430100',
     *            ),
     *          ),
     *        ),
     *      ),
     *    ),
     *),
     */
    public function getMap()
    {
        $this->__getAreaFileContents();
        return $this->areaFileContents;
    }

    /**
     * 获取地区的子节点
     *
     * @param Int $areaId 地区ID
     * @return 如果指定地区ID，则返回指定地区ID的所有子节点
     *         如果没有指定,则返回所有
     *
     * array (
     *   110000 =>
     *   array (
     *     0 => '110100',
     *   ),
     *   110100 =>
     *   array (
     *     0 => '110101',
     *     1 => '110102',
     *     2 => '110103',
     *     3 => '110104',
     *     4 => '110105',
     *   ),
     */
    public function getAreaIdPath($areaId)
    {
        if( $areaId )
        {
            $areaIds = $this->redis->hget('areaIdPath', $areaId);
            return  $areaIds ? json_decode($areaIds, true) : false;
        }

        $areaIdPath = $this->redis->hgetAll('areaIdPath');
        foreach( $areaIdPath as $id=>$row )
        {
            $areaIdPath[$id] = json_decode($row, true);
        }

        return $areaIdPath;
    }

    /**
     * 获取地区ID对应地区值
     *
     * @param Int $areaId 地区ID
     * @return 如果指定地区ID，则返回指定地区ID的名称和父节点ID
     *         如果没有指定,则返回所有
     * array (
     *  110100 =>
     *    array (
     *        'value' => '北京市',
     *        'parentId' => '110000',
     *    ),
     *  110101 =>
     *     array (
     *       'value' => '东城区',
     *       'parentId' => '110100',
     *    ),
     * )
     */
    public function areaKvdata($areaId)
    {
        if( $areaId )
        {
            $areaData = $this->redis->hget('areaKvdata', $areaId);
            return $areaData ? json_decode($areaData, true) : false;
        }

        $areaData = $this->redis->hgetAll('areaKvdata');
        foreach( $areaData as $areaId=>$row )
        {
            $areaData[$areaId] = json_decode($row, true);
        }

        return $areaData;
    }

    public function getAreaDataLv1()
    {
        $this->__getAreaFileContents();
        foreach( $this->areaFileContents as $row )
        {
            if( $row['disabled'] ) continue;
            $data[] = $row['id'];
        }
        return $data;
    }

    /**
     * 根据地区ID返回地区名称
     *
     * @param $areaId 地区ID
     */
    public function getAreaNameById($areaId)
    {
        $data = $this->redis->hget('areaKvdata', $areaId);
        return $data ? json_decode($data, true)['value'] : null;
    }

    /**
     * 三级联动选择，根据选择的地区ID返回地区名称
     *
     * @param $areaId 地区ID  以逗号隔开的地区ID
     * @param $type   地区分隔符
     *
     * return array | bool
     */
    public function getSelectArea($areaIds, $type='/')
    {
        foreach( explode(',',$areaIds) as $id )
        {
            if( $area = $this->getAreaNameById($id) )
            {
                $name[] = $area;
            }
        }

        $areaName = implode($type,$name);
        return $areaName;
    }

    /**
     * 检查联动的ID是否是合法的
     *
     * @param $areaId 地区ID  以逗号隔开的地区ID
     *
     * return bool
     */
    public function checkArea($areaIds)
    {
        $ids = explode(',',$areaIds);

        if( $this->redis->hget('areaIdPath', end($ids)) ) return false;

        foreach( $ids as $id )
        {
            if( $this->areaKvdata($id)['disabled'] ) return false;

            $areaIds = [];
            if( $parentId )
            {
                $areaIds = $this->redis->hget('areaIdPath', $parentId);
                if( !in_array($id, json_decode($areaIds, true)) ) return false;
            }

            $parentId = $id;
        }

        return true;
    }
}

