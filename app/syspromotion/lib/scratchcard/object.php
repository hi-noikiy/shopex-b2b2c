<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
class syspromotion_scratchcard_object {

    private $model = null;

    public function __construct()
    {
        $this->model = app::get('syspromotion')->model('scratchcard');
    }

    //获取活动的单调信息详情
    public function getScratchcard($scratchcardId, $userId = null)
    {
        $scratchcard = $this->model->getRow('*', ['scratchcard_id'=>$scratchcardId]);

        if($scratchcard && $userId)
            $userInfo['timesLimit'] = $this->getUserLimitTimes($scratchcard, $userId);
        return ['scratchcard'=>$scratchcard, 'userInfo'=>$userInfo];
    }

    private function getUserLimitTimes($scratchcard, $userId){
        $times = syspromotion_counter::instance('syspromotion.scratchcard.limit')
            ->readUserTimes($scratchcard['scratchcard_id'], $userId, $scratchcard['scratchcard_joint_limit']);
        return $times;
    }

    public function receiveScratchcard($scratchcardId, $userId, $rel_paymentid=null)
    {
        $scratchcard = $this->getScratchcard($scratchcardId);
        $scratchcard = $scratchcard['scratchcard'];
        foreach ($scratchcard['scratchcard_rules'] as $key => $value) {
            $arr[$key] = $value['rate'];
        }

        //扣减次数
        $leftTimes = $this->decr($scratchcard, $userId, $rel_paymentid);
        //随机中奖信息
        $prizeIndex = $this->getRand($arr);
        $prizeInfo = $scratchcard['scratchcard_rules'][$prizeIndex];
        if(!$prizeInfo['bonus_desc']){
           $this->getPrizeDesc($prizeInfo);
        }

        //未中奖
        if($prizeInfo['bonus_type'] == 'none')
        {
            return [
                'prizeInfo' => $prizeInfo,
                'leftTimes' => $leftTimes,
                'scratchcard' => $scratchcard,
                'scratchcard_result' => $prizeInfo,
            ];
        }

        $scratchcard_result = [];
        $scratchcard_result['scratchcard_id'] = $scratchcard['scratchcard_id'];
        $scratchcard_result['user_id'] = $userId;
        $scratchcard_result['rel_paymentid'] = $rel_paymentid;
        $scratchcard_result['scratchcard_name'] = $scratchcard['scratchcard_name'];
        $scratchcard_result['bonus_type'] = $prizeInfo['bonus_type'];
        $scratchcard_result['status'] = 'ready';
        $scratchcard_result['bonus_desc'] = $prizeInfo['bonus_desc'];
        $scratchcard_result['prizeInfo'] = $prizeInfo;


        try{
            $issueInfo = kernel::single('syspromotion_scratchcard_issue')->receive($scratchcard, $prizeInfo, $scratchcard_result);
        }catch(Exception $e){
            logger::error('scratchcart receive failed : ' . $e->__toString());

            $prizeInfo = $this->getNonePrize($scratchcard);
            return [
                'prizeInfo' => $prizeInfo,
                'leftTimes' => $leftTimes,
                'scratchcard' => $scratchcard,
                'scratchcard_result' => $prizeInfo,
            ];
        }

        $scratchcard_result['extend_data'] = $issueInfo;
        $scratchcard_result_id = kernel::single('syspromotion_scratchcard_result')->createResult($scratchcard_result);
        $scratchcard_result['result_id'] = $scratchcard_result_id;
        //返回信息
        $result = [
            'prizeInfo' => $prizeInfo,
            'leftTimes' => $leftTimes,
            'issueInfo' => $issueInfo,
            'scratchcard' => $scratchcard,
            'scratchcard_result' => $scratchcard_result,
        ];
        return $result;
    }

    //获取一个空奖，用于出错的时候
    private function getNonePrize($scratchcard)
    {
        $rules = $scratchcard['scratchcard_rules'];
        foreach($rules as $rule)
        {
            if($rule['bonus_type'] == 'none')
                return $rule;
        }
    }

    //获取奖项id算法
    private function getRand($proArr){
        $result = "";
        $proSum = array_sum($proArr);
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    public function decr($scratchcard, $userId, $rel_paymentid)
    {
        if($rel_paymentid){
            $times = syspromotion_counter::instance('syspromotion.scratchcard.trade.limit')
            ->tryVerify($scratchcard['scratchcard_id'], $rel_paymentid, 1);
        }
        $times = syspromotion_counter::instance('syspromotion.scratchcard.limit')
            ->tryVerify($scratchcard['scratchcard_id'], $userId, $scratchcard['scratchcard_joint_limit']);
        return $times;
    }


    public function exchangeScratchcard($scratchcardResultId)
    {
        $scratchcardResult = kernel::single('syspromotion_scratchcard_result')->getScratchcardResult($scratchcardResultId);
        if($scratchcardResult['status'] != 'ready') return true;
        kernel::single('syspromotion_scratchcard_issue')->exchange($scratchcardResult);
        kernel::single('syspromotion_scratchcard_result')->fireResult($scratchcardResultId);

        return true;
    }

    public function incr($scratchcard, $userId, $times)
    {
        $times = syspromotion_counter::instance('syspromotion.scratchcard.limit')
            ->addVerify($scratchcard['scratchcard_id'], $userId, $scratchcard['scratchcard_joint_limit'], $times);
        return $times;
    }


    //保存修改数据
    /**
    *  @params int scratchcard_id
    *  @params string scratchcard_name
    *  @params string scratchcard_desc
    *  @params string scratchcard_word
    *  @params string background_url
    *  @params string status
    *  @params string scratchcard_type
    *  @params int used_platform
    *  @params int scratchcard_joint_limit
    *  @params int scratchcard_point_num
    *  @params array scratchcard_rules
    ***/
    public function saveScratchcard($scratchcard)
    {
        $this->__checkScratchcardData($scratchcard);
        if( !$scratchcard['scratchcard_id'] )
        {
            $scratchcard['created_time'] = time();
        }
        $scratchcard['modified_time'] = time();

        return $this->model->save($scratchcard);

    }

    private function __checkScratchcardData($scratchcard)
    {

        if( count( $scratchcard['scratchcard_rules'] ) < 2 )
        {
            throw new \LogicException("至少添加两条奖项规则");

        }
        if( count( $scratchcard['scratchcard_rules'] ) > 8 )
        {
            throw new \LogicException("至多添加八条奖项规则");
        }

        $totalRate = 0;
        $noprizeRate = 0;
        $noprizeFlag = false;
        foreach( $scratchcard['scratchcard_rules'] as $v )
        {
            //判断未中奖概率是否为0
            if($v['bonus_type'] == 'none'){
                $noprizeFlag = true; // 标记未中奖项存在
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


        if( !$noprizeFlag )
        {
            throw new \LogicException("至少添加一条未中奖规则!");
        }

        if ($noprizeRate <=0) {
            throw new \LogicException("未中奖概率不能为0！");
        }

        if($params['status'] == 'active'){
            $activeScratchcard = $this->model->count(['status'=>'active']);
            if( $activeScratchcard >= 5 ){
                throw new Exception(app::get('syspromotion')->_('最多开启5活动！'));
            }
        }
        return $params;
    }

    private function getPrizeDesc(&$prizeInfo){
        switch ($prizeInfo['bonus_type']) {
            case 'hongbao':
                $prizeInfo['bonus_desc'] = $prizeInfo['hongbaomoney'].' 元红包';
                break;
            case 'point':
                $prizeInfo['bonus_desc'] = $prizeInfo['num'].' 积分';
                break;
            case 'voucher':
                $apiParams = [
                    'voucher_id' => $prizeInfo['voucher_id'],
                    'fields' => 'voucher_id, voucher_name,deduct_money',
                ];
                $voucherInfo = app::get('syspromotion')->rpcCall('promotion.voucher.get',$apiParams);
                $prizeInfo['bonus_desc'] = $voucherInfo['deduct_money'].'元购物券';
                break;
        }
    }

    public function stopActive($scratchcardId)
    {
        if($scratchcardId > 0)
            return $this->model->update(['status'=>'stop'], ['scratchcard_id'=>$scratchcardId]);
        throw new LogicException('刮刮卡编号格式错误');
    }
}

