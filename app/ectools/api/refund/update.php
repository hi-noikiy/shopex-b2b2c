<?php
// refund.update
class ectools_api_refund_update{

    public $apiDescription = '退款单状态更新';

    public function getParams()
    {
        $return['params'] = array(
            'refund_id' => ['type'=>'numeric','valid'=>'required', 'description'=>'退款单编号'],
            'status' => ['type'=>'string','valid'=>'required|in:ready,progress,succ,failed,cancel', 'description'=>'退款状态'],
        );
        return $return;
    }

    public function update($params)
    {
        $db = app::get('ectools')->database();
        $db->beginTransaction();
        try
        {
            $objMdlRefunds = app::get('ectools')->model('refunds');
            $refundInfo = $objMdlRefunds->getRow('status', ['refund_id'=>$params['refund_id']]);
            if($refundInfo['status']==$params['status'])
            {
                return true;
            }
            $data = [
                'status'=>$params['status'],
                'finish_time'=>time(),
            ];
            $filter = [
                'refund_id'=>$params['refund_id']
            ];
            $result = $objMdlRefunds->update($data, $filter);
            if(!$result)
            {
                throw new \LogicException("更新退款单状态失败");
                return false;
            }
            $db->commit();
        }
        catch(\Exception $e)
        {
            $db->rollback();
            throw new \LogicException("更新退款单状态失败");
        }
        return true;
    }

}
