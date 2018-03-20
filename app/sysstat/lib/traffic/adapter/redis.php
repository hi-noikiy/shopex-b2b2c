<?php
/**
 * file
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysstat_traffic_adapter_redis implements sysstat_interface_trafficStorage
{
    /**
     * 生成网站流量统计log文件
     * 
     */
    public function save($params)
    {
        $date = date('Ymd');
        $expiretime = strtotime(date('Y-m-d 00:00:00', strtotime('+1 day')));
        $params['pageflag'] = $params['page'].'|'.$params['page_rel_id'];
        $params['pvflag'] = $params['pageflag'].'|'.$params['use_platform'];
        $params['shopflag'] = $date.':'.$params['shop_id'];

        //统计商品的uv，不区分来源
        if($params['page'] =='item'){
            $use_platform =['pc','wap'];
            foreach ($use_platform as $value) {
                $uv[$value] = redis::scene('traffic')->zscore($date.':'.$params['pageflag'].'|'.$value,$params['remote_addr']);
            }
            $is_exist = array_sum($uv);
            if(!$is_exist){
                redis::scene('traffic')->zincrby('itemuv:'.$params['pageflag'],1,$date);
            }
        }
        //remote_ip log
        redis::scene('traffic')->zincrby($date.':'.$params['pvflag'],1,$params['remote_addr']);
        redis::scene('traffic')->expireat($date.':'.$params['pvflag'],$expiretime);



        //折线图数据
        $is_ip_exist = redis::scene('traffic')->hexists($date.':iplist',$params['remote_addr'].'_'.$params['shop_id'].'_'.$params['use_platform']);
        if($is_ip_exist<=0){
            redis::scene('traffic')->hmset($date.':iplist',$params['remote_addr'].'_'.$params['shop_id'].'_'.$params['use_platform'],1);
            redis::scene('traffic')->hincrby('webuv:all_'.$params['shop_id'],$date,1);
            redis::scene('traffic')->hincrby('webuv:'.$params['use_platform'].'_'.$params['shop_id'],$date,1);
        }
        redis::scene('traffic')->expireat($date.':iplist',$expiretime);


        //页面uv，pv统计
        $uv = redis::scene('traffic')->zcard($date.':'.$params['pvflag']);
        $pv = array_sum(redis::scene('traffic')->zrange($date.':'.$params['pvflag'], 0, -1, "WITHSCORES"));
        // redis::scene('traffic')->zadd('uv:'.$params['pvflag'],$uv,$date);
        // redis::scene('traffic')->zadd('pv:'.$params['pvflag'],$pv,$date);
        // //设置过期时间为30天
        // $exptime = strtotime(date('Ymd 23:59:59', strtotime('last month')));
        // redis::scene('traffic')->expireat('uv:'.$params['pvflag'],$exptime);
        // redis::scene('traffic')->expireat('pv:'.$params['pvflag'],$exptime);

        //每天的uvlist、pvlist
        redis::scene('traffic')->zadd($params['shopflag'].':uvlist',$uv,$params['pvflag']);
        redis::scene('traffic')->zadd($params['shopflag'].':pvlist',$pv,$params['pvflag']);
        $exp = strtotime(date('Ymd 23:59:59', strtotime('+1 month')));
        redis::scene('traffic')->expireat($params['shopflag'].':uvlist',$exp);
        redis::scene('traffic')->expireat($params['shopflag'].':pvlist',$exp);
    }

    /**
     *获取统计数据
     *
     **/
    public function getData($params){
        $selecttime = $this->__checkTime($params['timeType'],$params['starttime']);

        //获取所有页面流量分析数据
        if($params['dataType'] =='graphall'){
            $datas = $this->graphdata($params,$selecttime);
            return $datas;
        }

        //获取商品页面流量分析数据
        if($params['inforType']=='item')
        {
            $ids = explode(',', $params['itemids']);
            $exp = date('Ymd', strtotime('-3 month'));

            $uvData = [];
            foreach ($ids as $item_id) {
                $itemData = redis::scene('traffic')->zrange('itemuv:item|'.$item_id,0,-1,"WITHSCORES");
                foreach ($itemData as $date => $uv) {
                    if($date<$selecttime['start'] || $date >$selecttime['end']){
                        unset($itemData[$date]);
                    }elseif ($date<$exp) {
                        redis::scene('traffic')->zrem('itemuv:item|'.$item_id,$date);
                    }
                }
                $uvData[$item_id] = array_sum($itemData);
            }
            return $uvData;
        }
        elseif($params['inforType'] =='yesterdayRank')
        {
            //昨日流量统计
            $date = date('Ymd', strtotime('-1 day'));
            $end = $params['start']+$params['limit']-1;
            $uvlist = redis::scene('traffic')->zrevrange($date.':'.$params['shop_id'].':uvlist',$params['start'],$end, "WITHSCORES");
            foreach ($uvlist as $key => $value) {
                $pvflag = explode('|', $key);
                $data['page'] = $pvflag[0];
                $data['page_rel_id'] = $pvflag[1];
                $data['use_platform'] = $pvflag[2];
                $data['uv'] = $value;
                $data['pv'] = redis::scene('traffic')->zscore($date.':'.$params['shop_id'].':pvlist',$key);
                $datas['trafficData'][] = $data;
            }
            $datas['count'] = redis::scene('traffic')->zcard($date.':'.$params['shop_id'].':uvlist');

            return $datas;
        }else{
            //运营概况数据
            if($params['timeType'] =='yesterday' || $params['timeType'] =='beforday'){
                $com = $this->__checkTime($params['timeType'],$params['starttime']);
                $bef =date('Ymd', strtotime('-1 week', strtotime($com['start'])));
 
                $datas['commonday'] = redis::scene('traffic')->hget('webuv:all_'.$params['shop_id'],$com['start']);
                $datas['beforeweek'] = redis::scene('traffic')->hget('webuv:all_'.$params['shop_id'],$bef);
            }
            $alluvdata = redis::scene('traffic')->hgetall('webuv:all_'.$params['shop_id']);
            if($alluvdata){
                ksort($alluvdata,SORT_NUMERIC);
                $datas['commonday'] = 0;
                foreach ($alluvdata as $key => $value) {
                    if($key >= $selecttime['start'] && $key <= $selecttime['end']){
                        $datas['commonday'] += $value;
                    }
                }
            }
            return $datas;
        }
    }

    public function graphdata($params,$selecttime){
        $exp = date('Ymd', strtotime('last month'));
        $alluv['label'] ='全部UV';
        $alluvdata = redis::scene('traffic')->hgetall('webuv:all_'.$params['shop_id']);
        $pcuv['label'] ='电脑端UV';
        $pcuvdata = redis::scene('traffic')->hgetall('webuv:pc_'.$params['shop_id']);
        $wapuv['label'] ='移动端UV';
        $wapuvdata = redis::scene('traffic')->hgetall('webuv:wap_'.$params['shop_id']);

        if($alluvdata){
            ksort($alluvdata,SORT_NUMERIC);
            foreach ($alluvdata as $key => $value) {
                if(!isset($pcuvdata[$key])){
                    $pcuvdata[$key] = 0;
                }
                if(!isset($wapuvdata[$key])){
                    $wapuvdata[$key] = 0;
                }
                if($key >= $selecttime['start'] && $key <= $selecttime['end']){
                    $alluv['data'][] = array(
                        '0'=>strtotime($key)*1000,
                        '1'=> $value?$value:0,
                    );
                }elseif ($key<$exp) {
                    //删除过期数据
                    redis::scene('traffic')->hdel('webuv:all_'.$params['shop_id'],$key);
                }
            }
        }

        if($pcuvdata){
            ksort($pcuvdata,SORT_NUMERIC);
            foreach ($pcuvdata as $k => $v) {
                if($k >= $selecttime['start'] && $k <= $selecttime['end']){
                    $pcuv['data'][] = array(
                        '0'=>strtotime($k)*1000,
                        '1'=> $v?$v:0,
                    );
                }elseif ($key<$exp) {
                    redis::scene('traffic')->hdel('webuv:pc_'.$params['shop_id'],$key);
                }
            }
        }

        if($wapuvdata){
            ksort($wapuvdata,SORT_NUMERIC);
            foreach ($wapuvdata as $date => $uv) {
                if($date >= $selecttime['start'] && $date <= $selecttime['end']){
                    $wapuv['data'][] = array(
                        '0'=>strtotime($date)*1000,
                        '1'=> $uv?$uv:0,
                    );
                }elseif ($key<$exp) {
                    redis::scene('traffic')->hdel('webuv:wap_'.$params['shop_id'],$key);
                }
            }
        }

        $datas = [$alluv,$pcuv,$wapuv];
        return $datas;
    }

    public function __checkTime($type,$filter=null)
    {
        switch ($type) {
            case 'yesterday':
                return array('start'=>date('Ymd', strtotime('-1 day')),
                                'end'=>date('Ymd', strtotime('-1 day')),
                    );
                break;
            case 'beforday':
                return array('start'=>date('Ymd', strtotime('-2 day')),
                                'end'=>date('Ymd', strtotime('-2 day')),
                    );
                break;
            case 'beforeweek':
                return date('Ymd', strtotime('-8 day'));
                break;
            case 'week':
                return array('start'=>date('Ymd', strtotime('-7 day')),
                                'end' => date('Ymd', strtotime('-1 day')),
                );
                break;
            case 'month':
                return array('start'=>date('Ymd', strtotime('-30 day')),
                                'end' => date('Ymd', strtotime('-1 day'))
                    );
                break;
        }
    }
}