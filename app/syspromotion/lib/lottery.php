<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
class syspromotion_lottery
{

    public function __construct()
    {
        $this->objMdlLottery = app::get('syspromotion')->model('lottery');
    }

    private function checkLotteryParams($params)
    {
        $lotteryCount = $this->objMdlLottery->count();
        if( $lotteryCount >= 20 )
        {
            throw new \LogicException('最多添加20个转盘活动');
        }

        if(isset($params['lotteryrules']['key'])){
            unset($params['lotteryrules']['key']);
        }
        if(count($params['lotteryrules'])<2)
        {
            throw new \LogicException("至少添加两条奖项规则");
            
        }
        if(count($params['lotteryrules'])>8)
        {
            throw new \LogicException("至多添加八条奖项规则");
            
        }
        
        if(!in_array('none',array_column($params['lotteryrules'], 'bonus_type' ))){
            throw new \LogicException("至少添加一条未中奖规则!");   
        }

        $totalRate = 0;
        $noprizeRate = 0;
        foreach( $params['lotteryrules'] as $v )
        {
            //判断未中奖概率是否为0
            if($v['bonus_type'] == 'none'){
                if(isset($v['rate'])){
                    $noprizeRate += $v['rate'];
                } 
            }
            //计算所有奖项概率
            if(isset($v['rate'])){
                $totalRate +=  $v['rate']*1000;
            }

            if($v['bonus_type'] =='hongbao'){
                if(!$v['hongbaoid'] || !$v['hongbaomoney']){
                    throw new Exception(app::get('syspromotion')->_('请选择关联红包！'));
                }
            }
        }
        if ($totalRate != 100000) {
            throw new \LogicException('所有奖项概率之和必须为100%！');
        }


        if ($noprizeRate <=0) {
            throw new \LogicException("未中奖概率不能为0！"); 
        }

        if($params['status'] == 'active'){
            $count = $this->objMdlLottery->count(['status'=>'active']);
            if($count >=5){
                throw new Exception(app::get('syspromotion')->_('最多开启5个活动！'));
            }
        }
        return $params;
    }

    /**
     * 保存转盘抽奖规则
     */
    public function save($params)
    {
        $params = $this->checkLotteryParams($params);
        if($params['lotteryrules']){
            $params['lottery_rules'] = serialize($params['lotteryrules']);
            unset($params['lotteryrules']);
        }
        if ($params['lottery_id']) {
            $params['modified_time'] = time();
        }else{
            $params['created_time'] = time();
            $params['modified_time'] = time();
        }
        
        $db = app::get('syspromotion')->database();
        $db->beginTransaction();
        try{
            $this->objMdlLottery->save($params);
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollback();
            throw $e;
        }

        return $lotteryId;
    }

    /**
     * 删除转盘抽奖活动
     */
    public function delete($params)
    {
    }

    /**
     * 获取单个转盘抽奖详情
     */
    public function getInfo($filter)
    {
        $data = $this->objMdlLottery->getRow('*',$filter);
        if($data){
            $data['lottery_rules'] = unserialize($data['lottery_rules']);
        }

        return $data;
    }
}

