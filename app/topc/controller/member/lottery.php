<?php
class topc_ctl_member_lottery extends topc_ctl_member
{
   public function prizeList()
   {
      $data = input::get();
      $userId = userAuth::id();
      $pageSize = $this->limit;
      $currentPage = $data['pages'] ? $data['pages'] : 1;

      $params = array(
         'page_no' =>intval($data['pages']),
         'page_size' => intval($pageSize),
         'user_id' => $userId,
         'bonus_type' => 'custom',
      );

      $data = app::get('topc')->rpcCall('lottery.result.list',$params);
      if($data['totalnum']>0){
         $total = ceil($data['totalnum']/$pageSize);
      }
      $pagedata['count'] = $data['totalnum'];
      $pagedata['prizeList'] = $data['datalist'];
      //分页
      $filter['pages'] = time();
      $pagedata['pagers'] = array(
         'link'=>url::action('topc_ctl_member_lottery@prizeList',$filter),
         'current'=>$currentPage,
         'total'=>$total,
         'token'=>$filter['pages'],
      );
	   $this->action_view = "lottery/index.html";
	   return $this->output($pagedata);
   	}

   	public function saveAddr(){
   		$data = input::get();
   		if($data['area']){
   			$area = app::get('topc')->rpcCall('logistics.area',array('area'=>$data['area'][0]));
   		}
         $addrData = array(
            'receiver_name' => $data['name'],
            'receiver_area' => $area,
            'addr' => $data['addr'],
            'receiver_zip' => $data['zip'],
            'receiver_phone'=>$data['mobile'],
            'result_id' => $data['result_id'],
            'lottery_id' => $data['lottery_id'],
         );

   		$prizeList['addrList'] = array(
   			'result_id' => $data['result_id'],
   			'area' => str_replace("/","", $area),
   			'addr' => $data['addr'],
   			'zip' => $data['zip'],
   			'name' => $data['name'],
   			'mobile' => $data['mobile']
   		);
         try{
            $data = array(
               'user_id'=>userAuth::id(),
               'lottery_id'=>$data['lotteryid'],
               'result_id'=>$data['resultid'],
               'addrData' => $addrData,
            );

            $result = app::get('topc')->rpcCall('promotion.lottery.updateAddr', $data);
         }
         catch(Exception $e)
         {
            $prizeList['addrList']['error'] = true;
            $prizeList['addrList']['message'] = $e->getMessage();
            return response::json($prizeList['addrList']);
         }
         $prizeList['addrList']['success'] = true;
   		return response::json($prizeList['addrList']);
   	}
}

