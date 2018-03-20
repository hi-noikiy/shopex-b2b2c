<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
//定额红包

class syspromotion_hongbao_fixed extends syspromotion_abstract_hongbao{

    /**
     * 将生成红包list结构保存到Redis
     *
     * @param int $hongbaoId
     * @param array $data
     */
    public function hongbaolistSetToRedis($hongbaoId, $data)
    {
        foreach( $data['hongbao_list'] as $key=>$value )
        {
            $money = $value['money'];
            $num = $value['num'];
            $this->setRedis($this->createPayload('hongbaolist',$hongbaoId, $money), intval($num));
        }

        //保存红包总金额
        $this->setRedis($this->createPayload('totalMoney',$hongbaoId), $data['total_money'],false);
        $this->setRedis($this->createPayload('totalNum',$hongbaoId), intval($data['total_num']));
        return true;
    }

    /**
     * 给用户发放红包
     */
    public function getUserHongbao($userId, $money, $hongbaoData)
    {
        //用户红包金额  判断用户是否有领取资格
        $userGetHongbaoMoney = $this->baseHongbaoType->execRedisCommad('incrby', $this->createPayload('userGetMoney', $hongbaoData['hongbao_id'], $userId), $this->getmoney($money));
        if( $userGetHongbaoMoney > $hongbaoData['user_total_money']*100 )
        {
            throw new \LogicException(app::get('syspromotion')->_('你已达到红包领取金额最大值'));
        }

        //用户红包数量
        $userGetHongbaoNum = $this->baseHongbaoType->execRedisCommad('incr', $this->createPayload('userGetNum', $hongbaoData['hongbao_id'], $userId) );
        if( $userGetHongbaoNum > $hongbaoData['user_total_num'] )
        {
            throw new \LogicException(app::get('syspromotion')->_('你已达到红包领取数量最大值'));
        }

        //领取红包
        $payloadId = $this->createPayload('hongbaolist', $hongbaoData['hongbao_id'], $money);
        $hongbaoTypeNum = $this->baseHongbaoType->execRedisCommad('decr', $payloadId);
        if( $hongbaoTypeNum < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包已被领完') );
        }

        $hongbaoId = $hongbaoData['hongbao_id'];
        //红包总量剩余金额
        $totalMoney = $this->baseHongbaoType->execRedisCommad('decrby', $this->createPayload('totalMoney',$hongbaoId), $this->getmoney($money));
        //红包总量剩余数量
        $totalNum = $this->baseHongbaoType->execRedisCommad('decr', $this->createPayload('totalNum',$hongbaoId));
        if( $totalMoney < 0 || $totalNum < 0 )
        {
            throw new \LogicException(app::get('syspromotion')->_('红包已被领完'), 200);
        }

        return $money;
    }

}

