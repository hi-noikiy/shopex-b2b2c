<?php

class systrade_mdl_cart extends dbeav_model {

    public $defaultOrder = array('created_time',' DESC');

    public function getList( $cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null )
    {
        $userId = $filter['user_id'];
        if( $userId ) $filter['user_ident'] = $this->getUserIdentMd5($userId);

        //user_id有直接获取user_id, 改为通过传入的方式，为保证正确传入或者漏传，该处先直接报错用于找到原因
        if( ! $filter['cart_id'] && !$filter['user_ident'] )
        {
            throw new \LogicException('购物车数据获取失败');
        }

        return parent::getList( $cols, $filter, $offset, $limit, $orderType );
    }

    /**
     * @brief 生成唯一的用户标识
     *
     * @return 返回md5的值
     */
    public function getUserIdentMd5($userId=null)
    {
        if( $userId )
        {
            return md5($userId);
        }
        else
        {
            return $this->getSessionUserIdent();
        }
    }

    public function getSessionUserIdent()
    {
        $str = kernel::single('base_session')->sess_id();
        return md5($str);
    }

}

