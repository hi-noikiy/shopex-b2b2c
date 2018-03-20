<?php
/**
 * ShopEx licence
 * - user.hongbao.get
 * - 用户领取红包接口
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  www.ec-os.net ShopEx License
 */
final class sysuser_api_user_hongbao_receiveTmpHongbao {

    /**
     * 接口作用说明
     */
    public $apiDescription = '用户从暂存空间领走红包';

    /**
     * 接口参数
     */
    public function getParams()
    {
        $return['params'] = array(
            'user_id'       => ['type'=>'string', 'valid'=>'required',  'title'=>'用户ID',       'desc'=>'用户ID'],
            'tmphongbao_id' => ['type'=>'string', 'valid'=>'required',  'title'=>'红包ID',       'desc'=>'红包ID'],
        );
        return $return;
    }

    /**
     * @return int user_hongbao_id
     */
    public function receive($params)
    {

        $userId        = $params['user_id'];
        $tmpHongbaoId  = $params['tmphongbao_id'];


        $tmpHongbaoMdl = app::get('sysuser')->model('user_hongbao_tmp');
        $hongbaoMdl = app::get('sysuser')->model('user_hongbao');

        try{
  //        $db = app::get('sysuser')->database();
  //        $transaction_status = $db->beginTransaction();

            $hongbao = $tmpHongbaoMdl->getRow('*', ['user_id'=>$userId, 'id'=>$tmpHongbaoId]);
            if($hongbao)
            {
                unset($hongbao['id']);
                $tmpHongbaoMdl->delete(['user_id'=>$userId, 'id'=>$tmpHongbaoId]);
                $userHongbao_id = $hongbaoMdl->insert($hongbao);
                if(!$userHongbao_id)
                    throw new LogicException( app::get('sysuser')->_('红包认领失败') );

  //            $db->commit($transaction_status);

            }else{
                throw new LogicException( app::get('sysuser')->_('未找到这个红包~') );
            }
        }catch(Exception $e){
  //        $db->rollback();
            throw $e;
        }

        return ['user_hongbao_id' => $userHongbao_id];
    }
}

