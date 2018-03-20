<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_desktop_storeListData
{
     /**
     * 获取公共数据
     * data  页面传过来的数据
     * @return array
     */
    public function getCommonData($data)
    {
        if(strtotime($data['time_start'])>strtotime($data['time_end']))
        {
            throw new \LogicException(app::get('sysstat')->_("开始时间必须小于结束时间"));
        }

        if($data['timeType'])
        {
            $timeRange = kernel::single('sysstat_desktop_commonData')->getTimeRangeByType($data['timeType']);
            //$timeRange = $this->_getTimeRangeByType($data['timeType']);
            $timeStart = strtotime($timeRange['time_start']);
            $timeEnd = strtotime($timeRange['time_end']);
        }
        else
        {
            $timeStart = strtotime($data['time_start']);
            $timeEnd = strtotime($data['time_end']);
        }

        $dataType = $data['dataType']?$data['dataType']:'num';
        $limit = $data['storeLimit']?$data['storeLimit']:5;
        //获取店铺排行数据
        $storeListInfo = $this->_getStoreListData($dataType,$timeStart,$timeEnd,$limit);
       //echo '<pre>';print_r($tradeData);exit();
        $pagedata['storeListData'] = $storeListInfo;
        $pagedata['time_start'] = date('Y/m/d',$timeStart);
        $pagedata['time_end'] = date('Y/m/d',$timeEnd);
        return $pagedata;
    }

    /**
     * @brief  获取交易数据
     * $dataType 数据类型  是件数num,还是钱money,string
     * $timeStart 查询的开始时间 2015-03-01
     * $timeEnd 查询的结束时间2015-03-03
     * 
     * @return array
     */
    private function _getStoreListData($dataType,$timeStart,$timeEnd,$limit)
    {
        $mdlDesktopTradeStat = app::get('sysstat')->model('desktop_stat_shop');
        if($dataType=='num')
        {
            $orderBy = 'shopaccountnum';
        }
        if($dataType=='money')
        {
            $orderBy = 'shopaccountfee';
        }
        $filter = array(
            'timeStart'=>$timeStart,
            'timeEnd'=>$timeEnd
        );
        if(!$limit)
        {
            $limit = -1;
        }
        $fileds = 'shop_id,shopname,shopaccountfee,shopaccountnum,createtime';
        //echo '<pre>';print_r($orderBy);exit();
        $storeListData = $mdlDesktopTradeStat->getStoreList($fileds,$filter,0,$limit,$orderBy);
        return $storeListData;
    }

}
