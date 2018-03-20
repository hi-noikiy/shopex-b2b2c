<?php

class systrade_cart_coupon_redis
{
    private $scene = 'systrade';

    private $prefix = 'cart:useCoupon';

    public function get($userId, $shopId)
    {
        $keyName = $this->__genKeyName($userId);
        $key = $this->__genKey($shopId);
        return redis::scene($this->scene)->hget($keyName, $key);
    }

    public function set($userId, $shopId, $couponCode)
    {
        $keyName = $this->__genKeyName($userId);
        $key = $this->__genKey($shopId);
        redis::scene($this->scene)->hset($keyName, $key, $couponCode);
        return true;
    }

    public function del($userId, $shopId)
    {
        $keyName = $this->__genKeyName($userId);
        $key = $this->__genKey($shopId);
        redis::scene($this->scene)->hdel($keyName, $key);
        return true;
    }

    public function clean($userId)
    {
        $keyName = $this->__genKeyName($userId);
        redis::scene($this->scene)->del($keyName);
        return true;
    }


    private function __genKeyName($userId)
    {
        return $this->prefix .':'. app::get('systrade')->model('cart')->getUserIdentMd5($userId);
    }

    private function __genKey($shopId)
    {
        return $shopId;
    }
}

