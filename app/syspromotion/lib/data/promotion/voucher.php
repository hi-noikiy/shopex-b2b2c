<?php
//平台购物券
class syspromotion_data_promotion_voucher {

    public $redisCommand = [];

    /**
     * 保存或编辑购物券，将购物券能生成最大的数量同步到redis中
     */
    public function initVoucherToRedis($voucherId, $maxGenQuantity)
    {
        //购物券剩余总数量
        redis::scene('voucher')->hset('list:'.$voucherId, 'voucher_quantity', $maxGenQuantity);
        return true;
    }

    /**
     * 保存或编辑购物券，将购物券能生成最大的数量同步到redis中
     */
    public function updateUsedQuantity($voucherId, $quantity)
    {
        $useVouchercodeQuantity = redis::scene('voucher')->hincrby('list:'.$voucherId, 'use_vouchercode_quantity', $quantity);

        $objMdlVoucher = app::get('syspromotion')->model('voucher');
        $objMdlVoucher->update(['use_vouchercode_quantity'=>$useVouchercodeQuantity], ['voucher_id'=>$voucherId]);

        return $useVouchercodeQuantity;
    }


    /**
     * 给用户发放购物券 发放单个
     *
     * @param $userId int 发放给用户的ID
     * @param $voucherId int 购物券ID
     * @param $$gradeId int 会员等级
     */
    public function getVoucherCode($userId, $voucherId, $gradeId)
    {
        $redis = redis::scene('voucher');
        $objMdlVoucher = app::get('syspromotion')->model('voucher');

        $voucherInfo = $objMdlVoucher->getRow('*', ['voucher_id'=>$voucherId]);

        //判断购物券是否有效
        if( !$voucherInfo || !$voucherInfo['valid_status'] )
        {
            throw new LogicException('无效购物券');
        }

        //当前时间是否在领取时间范围内
        if( $voucherInfo['cansend_end_time'] < time() || $voucherInfo['cansend_start_time'] > time() )
        {
            throw new LogicException('当前时间不可购物券领取');
        }

        $validGrade = explode(',', $voucherInfo['valid_grade']);
        if(!in_array($gradeId, $validGrade))
        {
            throw new \LogicException('您的会员等级不可以领取此购物券');
        }

        try
        {
            //判断购物券剩余总数，是否还能发放
            $voucherQuantity = $this->__checkVoucherQuantity($redis, $voucherId);

            //判断用户领取限制，是否已达最大值
            $userQuantity = $this->__checkUserVoucher($redis, $userId, $voucherId, $voucherInfo['userlimit_quantity']);

            //已生成购物券数量
            $sendVouchercodeQuantity = $voucherInfo['max_gen_quantity'] - $voucherQuantity;

            //生成购物券
            $code = $this->__genVoucherCode($voucherInfo, $sendVouchercodeQuantity);

            //更新购物券信息
            $objMdlVoucher->update(['send_vouchercode_quantity'=>$sendVouchercodeQuantity], ['voucher_id'=>$voucherId]);
        }
        catch( Exception $e)
        {
            if( $this->redisCommand )
            {
                foreach( $this->redisCommand as $row )
                {
                    call_user_func_array($row, array());
                }
            }
            throw $e;
        }

        $voucherInfo['code'] = $code;
        return $voucherInfo;
    }

    private function __genVoucherCode($voucherInfo, $sendVouchercodeQuantity)
    {
        $iNo = str_pad($this->__dec2b36($sendVouchercodeQuantity), 4, '0', STR_PAD_LEFT);
        $key = str_random(4). str_shuffle(substr(sha1(serialize($voucherInfo).$iNo), rand(0,26),5).$iNo);
        return  'V'. strtoupper($key);
    }

    private function __dec2b36($int)
    {
        $b36 = array(0=>"0",1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6",7=>"7",8=>"8",9=>"9",10=>"A",11=>"B",12=>"C",13=>"D",14=>"E",15=>"F",16=>"G",17=>"H",18=>"I",19=>"J",20=>"K",21=>"L",22=>"M",23=>"N",24=>"O",25=>"P",26=>"Q",27=>"R",28=>"S",29=>"T",30=>"U",31=>"V",32=>"W",33=>"X",34=>"Y",35=>"Z");
        $retstr = "";
        if($int>0)
        {
            while($int>0)
            {
                $retstr = $b36[($int % 36)].$retstr;
                $int = floor($int/36);
            }
        }
        else
        {
            $retstr = "0";
        }

        return $retstr;
    }

    //判断用户领取限制，是否已达最大值
    private function __checkUserVoucher($redis, $userId, $voucherId, $limit)
    {
        //在用户限制表中是否存在，如果存在者不能在此领取
        if( $redis->hget('list:'.$voucherId, 'user_end_'.$userId) )
        {
            throw new LogicException('购物券领取已达最大限制');
        }

        $userQuantity = $redis->hincrby('list:'.$voucherId, 'user_quantity_'.$userId, 1);
        //如果用户领取超出限制
        if( $userQuantity > $limit )
        {
            //加入到限制表中
            $redis->hset('list:'.$voucherId, 'user_end_'.$userId, true);
            $redis->hincrby('list:'.$voucherId, 'user_quantity_'.$userId, -1);
            throw new LogicException('购物券领取已达最大限制');
        }

        $this->redisCommand[] = function () use ($redis, $voucherId, $userId) {
            return call_user_func_array(
                [$redis, 'hincrby'], array('list:'.$voucherId, 'user_quantity_'.$userId, -1)
            );
        };

        return $userQuantity;
    }

    //判断购物券是否发完
    private function __checkVoucherQuantity($redis, $voucherId)
    {
        if( $redis->hget('list:'.$voucherId, 'voucher_quantity') > 0 )
        {
            //减去购物券剩余总数
            $voucherQuantity = $redis->hincrby('list:'.$voucherId, 'voucher_quantity', -1);
            if( $voucherQuantity < 0 )
            {
                $redis->hincrby('list:'.$voucherId, 'voucher_quantity', 1);
                throw new LogicException('购物券已发完');
            }
        }
        else
        {
            throw new LogicException('购物券已发完');
        }

        $this->redisCommand[] = function () use ($redis, $voucherId) {
            return call_user_func_array(
                [$redis, 'hincrby'], array('list:'.$voucherId, 'voucher_quantity', 1)
            );
        };

        //返回剩余购物券总数
        return $voucherQuantity;
    }
}

