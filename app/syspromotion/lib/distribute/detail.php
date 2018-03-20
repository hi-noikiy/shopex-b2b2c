<?php

class syspromotion_distribute_detail
{

    private $__model ;
    private $__db ;

    public function __construct()
    {
        $this->__model = app::get('syspromotion')->model('distribute_detail');
        $this->__db = app::get('syspromotion')->database();
    }

    public function create($distribute, $user)
    {

        $transaction_status = $this->__db->beginTransaction();

        $distributeDetail = $this->genDistributeDetail($distribute, $user);
        $distributeDetail['status'] = 'fine';
        $distributeDetail['created_time'] = time();

        try{
            $distributeDetail['discount_detail_param'] = kernel::single('syspromotion_distribute_plugin_instance')->getAdapter($distributeDetail['discount_type'])->receive($distributeDetail);

            $distributeDetailId = $this->__model->insert($distributeDetail);
            $distributeDetail['distribute_detail_id'] = $distributeDetailId;

            $this->__db->commit($transaction_status);

            try{
                $params['user'] = $user;
                $params['distribute'] = $distribute;
                $params['detail'] = $distributeDetail;
                event::fire('syspromotion.distribute.detail.send', [$params]);
            }catch(Exception $e) {
                logger::error('distribute send has some error with send notify : ' . $e->__toString());
            }

            return $distributeDetail;
        }catch(Exception $e){

            try{
                if($distributeDetailId > 0)
                {
                    $this->updateExceptionMsgById($distributeDetailId, $e->getMessage());
                }else{
                    $distributeDetail['status'] = 'exception';
                    $distributeDetail['exceptionMsg'] = $e->getMessage();
                    $distributeDetailId = $this->__model->insert($distributeDetail);
                }
                $this->__db->commit($transaction_status);
                logger::error('distribute send has some error : ' . $e->__toString());

                return $distributeDetail;
            }catch(Exception $e){
                logger::error('distribute send has many error : ' . $e->__toString());
            }
        }
    }

    public function genDistributeDetail($distribute, $user)
    {
        $distributeDetail = [];
        $distributeDetail['distribute_id'] = $distribute['distribute_id'];
        $distributeDetail['user_id'] = $user['user_id'];
        $distributeDetail['discount_type'] = $distribute['discount_type'];
        $distributeDetail['discount_param'] = $distribute['discount_param'];

        return $distributeDetail;
    }

    public function updateDistributeDetailParamsById($distributeDetailId, $params)
    {
        return true;
    }

    public function updateDistributeDetailParamsByUser($distributeId, $userId, $params)
    {
        return true;
    }

    public function updateExceptionMsgById($distributeDetailId, $msg)
    {
        $detail = [];
        $detail['status'] = 'exception';
        $detail['exceptionMsg'] = $msg;
        $this->__model->update( $detail , ['distribute_detail_id'=>$distributeId]);
        return true;
    }

    public function updateExceptionMsgByUser($distributeId, $userId, $msg)
    {
        return true;
    }

}

