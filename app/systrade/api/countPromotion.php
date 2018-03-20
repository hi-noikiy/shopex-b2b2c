<?php
class systrade_api_countPromotion{

    public $apiDescription = '获取某促销的使用次数';
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'promotion_id' => ['type'=>'string', 'valid'=>'required', 'default'=>'', 'example'=>'','description'=>'促销id'],
            'user_id' => ['type'=>'string', 'valid'=>'', 'default'=>'', 'example'=>'','description'=>'用户id'],
        );
        return $return;
    }

    public function countPromotion($params)
    {
        if($params['user_id'])
        {
            $user_id = intval($params['user_id']);
        }
        elseif($params['oauth']['account_id'])
        {
            $user_id = $params['oauth']['account_id'];
        }
        else
        {
            throw new Exception('用户的user_id参数缺失!');
        }

        $objMdlPromDetail = app::get('systrade')->model('promotion_detail');
        $filter = array('promotion_id'=>$params['promotion_id'], 'user_id'=>$user_id);
        $tids = $objMdlPromDetail->getList('tid', $filter);

        $count = 0;
        if( $tids )
        {
            $objMdlTrade = app::get('systrade')->model('trade');
            $tids = array_unique(array_column($tids, 'tid'));
            $count = $objMdlTrade->count(['tid'=>$tids, 'status|noequal'=>'TRADE_CLOSED_BY_SYSTEM']);
        }
        return $count;
    }
}
