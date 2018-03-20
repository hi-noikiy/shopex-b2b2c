<?php

class syspromotion_activity {


    /**
     * @brief 获取活动状态
     * @author gjp
     * @param $params array
     *
     * @return
     */
    public function getActivityStatus($params)
    {
        $objMdlActivityItem = app::get('syspromotion')->model('activity_item');
        $objMdlActivity = app::get('syspromotion')->model('activity');

        if($params['item_id'])
        {
            $filter = array(
                'item_id'=>$params['item_id'],
                'start_time|lthan'=>time(),
                'end_time|than'=>time()
            );
            $activity = $objMdlActivityItem->getRow('activity_id,verify_status',$filter);
            if($activity)
            {
                $activityInfo = $objMdlActivity->getRow('*',array('activity_id'=>$activity['activity_id']));

                if($activityInfo && $activityInfo['apply_begin_time'] < time() && time() < $activityInfo['end_time'] && $activity['verify_status']!='refuse')
                {
                    $result = 1;
                }
                else
                {
                    $result = 0;
                }
            }
            else
            {
                $result = 0;
            }
        }
        else
        {
            $result = 0;
        }
        //echo $result;exit();

        return $result;
        #code
    }
    /**
     * @brief 删除活动
     * @author lujy
     * @param $params array
     *
     * @return
     */
    public function deleteActivity($params)
    {
        $objMdlActivity = app::get('syspromotion')->model('activity');
        $list = $objMdlActivity->getList('activity_id,apply_begin_time',array('activity_id'=>$params));
        if($list)
        {
            $result = true;
            foreach($list as $key=>$value)
            {
                if($value['apply_begin_time'] < time())
                {
                    $result = false;
                    $msg = "活动报名已经开始，不可删除";
                }
                else
                {
                    $return = $objMdlActivity->delete(array('activity_id'=>$value['activity_id']));
                    if(!$return)
                    {
                        $result = false;
                        $msg = "删除失败";
                    }
                }
            }
            if(!$result)
            {
                throw new LogicException($msg);
            }
        }
        return true;
        #code
    }

    /**
     * 保存活动
     * @param  array $data 活动传入数据
     * @return bool       是否保存成功
     */
    public function saveActivity($data)
    {
        $activityData = $this->__preareData($data);
        $objMdlActivity = app::get('syspromotion')->model('activity');
        $objMdlActivityItem = app::get('syspromotion')->model('activity_item');

        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try
        {
            if( !$objMdlActivity->save($activityData) )
            {
                throw \LogicException('活动保存失败');
            }

            if($activityData['activity_id'] && $objMdlActivityItem->getRow('item_id',array('activity_id'=>$activityData['activity_id'])))
            {
                $updateResult = $objMdlActivityItem->update(['start_time'=>$activityData['start_time'],'end_time'=>$activityData['end_time']],['activity_id'=>$activityData['activity_id']]);
                if(!$updateResult)
                {
                    throw \LogicException('更新报名商品表的开始和结束时间出错');
                }
            }

            $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollback();
            throw $e;
        }
        return true;

    }

    private function __preareData($data) {
        $aResult = array();
        $aResult = $data;

        if($data['activity_id'])
        {
            $objMdlActivity = app::get('syspromotion')->model('activity');
            $activityInfo = $objMdlActivity->getRow('*',array('activity_id'=>$data['activity_id']));
            if( time() >= $activityInfo['start_time'] )
            {
                throw new \LogicException('活动生效开始时间后则不可进行编辑!');
            }
        }
        else
        {
            $aResult['created_time'] = time();
        }
        if(!$aResult['remind_enabled'])
        {
            unset($aResult['remind_time']);
        }
       /* if( $data['buy_limit'] <= 0 )
        {
            throw new \LogicException('用户限购数量要大于0!');
        }
        if( $data['discount_max'] <= $data['discount_min'])
        {
            throw new \LogicException('折扣范围必须由小到大！');
        }

        if($data['apply_begin_time'] < time())
        {
            throw new \LogicException('活动报名的开始时间必须大于当前时间！');
        }

        if( $data['apply_end_time'] <= $data['apply_begin_time'] )
        {
            throw new \LogicException('活动报名结束时间必须大于报名的开始时间！');
        }

        if( $data['release_time'] <= $data['apply_end_time']  )
        {
            throw new \LogicException('发布时间必须大于报名结束时间！');
        }

        if( $data['start_time'] <= $data['release_time'] )
        {
            throw new \LogicException('活动生效时间必须大于活动发布时间！');
        }

        if(  $data['end_time'] <= $data['start_time'] )
        {
            throw new \LogicException('活动生效结束时间必须大于活动开始时间！');
        }

        if( !$data['shoptype'])
        {
            throw new \LogicException('至少选择一种店铺类型！');
        }
        if( !$data['limit_cat'])
        {
            throw new \LogicException('至少选择一种平台商品类目！');
        }*/

        if($data['activity_name'])
        {
            $aResult['activity_name'] = strip_tags($data['activity_name']);
        }

        if($data['activity_desc'])
        {
            $aResult['activity_desc'] = strip_tags($data['activity_desc']);
        }

        if($data['shoptype'])
        {
            $aResult['shoptype'] = implode(',',$data['shoptype']);
        }
        // $forPlatform = intval($data['used_platform']);
        // $aResult['used_platform'] = $forPlatform ? $forPlatform : '0';
        return $aResult;
    }


    public function getList($row,$filter,$offset=0, $limit=200, $orderBy=null)
    {
        $objMdlActivity = app::get('syspromotion')->model('activity');
        $activity = $objMdlActivity->getList($row,$filter,$offset,$limit,$orderBy);
        return $activity;
    }

    public function countActivity($filter)
    {
        $objMdlActivity = app::get('syspromotion')->model('activity');
        return $objMdlActivity->count($filter);
    }

    public function countActivityItem($filter)
    {
        $objMdlActivityItem = app::get('syspromotion')->model('activity_item');
        return $objMdlActivityItem->count($filter);
    }

    public function getInfo($row,$filter)
    {
        $objMdlActivity = app::get('syspromotion')->model('activity');
        $activity = $objMdlActivity->getRow($row,$filter);

        //如果查询的字段中有店铺类型，需要显示店铺中文描述
        if(strpos($row,'shoptype') || $row == "*" || $row == "shoptype")
        {
            $shoptype = $activity['shoptype'];
            $activity['shoptype'] = $this->__getShopType($shoptype);
        }
        //如果查询的字段中包含类目，需要查询类目相关的所有内容
        if(strpos($row,'limit_cat') || $row == "*" || $row == "limit_cat")
        {
            $cat = $activity['limit_cat'];
            $activity['limit_cat'] = $this->__getCat($cat);
        }
        return $activity;
    }

    private function __getShopType($params)
    {
        // 获取店铺类型
        $shopType = app::get('syspromotion')->rpcCall('shop.type.get',array('shop_type'=>$params));
        foreach($shopType as $value)
        {
            $type[$value['shop_type']] = $value['name'];
        }
        return $type;
    }

    private function __getCat($params)
    {
        $params = implode(',',$params);
        //获取类目
        $cat = app::get('syspromotion')->rpcCall('category.cat.get.info',array('cat_id' => $params,'level' =>1));
        foreach($cat as $value)
        {
            $data[$value['cat_id']] = $value['cat_name'];
        }
        return $data;
    }

    public function getItemList($row,$filter,$offset=0, $limit=200, $orderBy=null)
    {
        $objMdlItemActivity = app::get('syspromotion')->model('activity_item');
        $activityItem = $objMdlItemActivity->getList($row,$filter,$offset,$limit,$orderBy);
        return $activityItem;
    }

    public function setMainpush($params)
    {
        $objMdlActivity = app::get('syspromotion')->model('activity');
        $result = $objMdlActivity->update(array( 'mainpush' => 0));
        if(!$result)
        {
            throw \LogicException("取消原有主推活动失败");
        }
        $params['mainpush'] = 1;
        $result = $objMdlActivity->save($params);
        if(!$result)
        {
            throw \LogicException("设置主推活动失败");
        }
        return true;
    }

}
