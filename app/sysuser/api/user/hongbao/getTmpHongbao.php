<?php
/**
 * ShopEx licence
 * - user.hongbao.get
 * - 用户领取红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class sysuser_api_user_hongbao_getTmpHongbao {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户领取红包到暂存空间的接口';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'            => ['type'=>'string', 'valid'=>'required',  'title'=>'用户ID',       'desc'=>'用户ID'],
            'hongbao_id'         => ['type'=>'string', 'valid'=>'required',  'title'=>'红包ID',       'desc'=>'红包ID'],
            'money'              => ['type'=>'string', 'valid'=>'required',  'title'=>'获取指定红包', 'desc'=>'获取指定红包'],
            'hongbao_obtain_type'=> ['type'=>'string', 'valid'=>'required|in:userGet',  'title'=>'获取红包方式', 'desc'=>'获取红包方式 userGet用户主动获取红包'],
        );
        return $return;
    }

    /**
     * 用户领取红包接口
     *
     * @user_tmp_hongbao_id 在暂存区的红包id
     * @return bool true
     */
    public function get($params)
    {
        $apiParams['user_id'] = $params['user_id'];
        $apiParams['hongbao_id'] = $params['hongbao_id'];
        $apiParams['money'] = $params['money'];
        $apiParams['hongbao_obtain_type'] = $params['hongbao_obtain_type'];
        $data = app::get('sysuser')->rpcCall('promotion.hongbao.issued',$apiParams);
        if( $data )
        {
            $objMdlUserHongbao = app::get('sysuser')->model('user_hongbao_tmp');
            $userHongbao['name'] = $data['name'];
            $userHongbao['user_id'] = $params['user_id'];
            $userHongbao['hongbao_id'] = $data['hongbao_id'];
            $userHongbao['hongbao_obtain_type'] = $params['hongbao_obtain_type'];
            $userHongbao['obtain_time'] = time();
            $userHongbao['used_platform'] = $data['used_platform'];
            $userHongbao['hongbao_type'] = $data['hongbao_type'];
            $userHongbao['money'] = $data['money'];
            $userHongbao['start_time'] = $data['use_start_time'];
            $userHongbao['end_time'] = $data['use_end_time'];

            $userHongbao_id = $objMdlUserHongbao->insert($userHongbao);
            if($userHongbao_id)
                return ['user_tmp_hongbao_id' => $userHongbao_id, 'user_tmp_hongbao_info'=>$userHongbao];
            throw new \LogicException(app::get('红包领取失败'));
        }
        else
        {
            throw new \LogicException(app::get('红包领取失败'));
        }
    }
}

