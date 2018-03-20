<?php

class systrade_cart_voucher
{
    private $prefix = 'cart:useVoucher';

    public function addVoucherCart($userId, $code, $platform)
    {
        //添加暂时不验证，使用购物券显示的都为满足条件的购物券
        $keyName = $this->__genKeyName($userId, $platform);
        redis::scene('voucher')->set($keyName, $code);
        return true;
    }

    public function getUseVoucher($userId, $platform)
    {
        $keyName = $this->__genKeyName($userId, $platform);
        $code = redis::scene('voucher')->get($keyName);
        if( $code )
        {
            $voucherInfo = app::get('sysuser')->rpcCall('user.voucher.get',['voucher_code'=>$code,'user_id'=>$userId]);
        }

        if( !$code || !$voucherInfo )
        {
            return 0;
        }

        if( $voucherInfo['start_time'] > time() )
        {
            throw new \LogicException('购物券还未开启');
        }

        if( $voucherInfo['end_time'] < time() )
        {
            throw new \LogicException('购物券已过期');
        }

        if( $voucherInfo['is_valid'] != '1' )
        {
            throw new \LogicException('无效购物券');
        }

        if( !in_array($platform, explode(',',$voucherInfo['used_platform'])) )
        {
            throw new \LogicException('购物券不支持当前平台');
        }

        return $voucherInfo;
    }

    public function cancelVoucherCart($userId, $platform)
    {
        $keyName = $this->__genKeyName($userId, $platform);
        return redis::scene('voucher')->del($keyName);
    }


    private function __genKeyName($userId, $platform)
    {
        return $platform.$this->prefix .':'. app::get('systrade')->model('cart')->getUserIdentMd5($userId);
    }
}

