<?php
class topwap_ctl_promotion_scratchcard extends topwap_controller{

    public function index()
    {

        $apiParams = [];
        $apiParams['scratchcard_id'] = input::get('scratchcard_id');
        if(!$apiParams['scratchcard_id'])
            return kernel::abort(404);

        $userId = userAuth::id();
        if($userId)
            $apiParams['user_id'] = $userId;

        $scratchcard = app::get('topwap')->rpcCall('promotion.scratchcard.get', $apiParams);
        $pagedata['scratchcard'] = $scratchcard;
        $pagedata['loginStatus'] = $userId ? 'true' : 'false';
        $pagedata['loginUrl'] = url::action('topwap_ctl_passport@goLogin');
        $pagedata['receiveUrl'] = url::action('topwap_ctl_promotion_scratchcard@receive', ['scratchcard_id'=>$apiParams['scratchcard_id']]);
        $pagedata['exchangeUrl'] = url::action('topwap_ctl_promotion_scratchcard@exchange');
        return $this->page('topwap/promotion/scratchcard/index.html', $pagedata);
    }

    public function receive()
    {
        $apiParams = [];
        $apiParams['scratchcard_id'] = input::get('scratchcard_id');
        $apiParams['user_id'] = userAuth::id();
        try{
            $receiveInfo = app::get('topwap')->rpcCall('promotion.scratchcard.receive', $apiParams);
        }catch(Exception $e){
            return response::json(['error'=>true, 'msg'=>$e->getMessage()]);
        }

        return response::json($receiveInfo);
    }

    public function exchange()
    {
        $apiParams['scratchcard_result_id'] = input::get('scratchcard_result_id');
        try{
            $exchangeInfo = app::get('topwap')->rpcCall('promotion.scratchcard.exchange', $apiParams);
        }catch(Exception $e){
            return response::json(['error'=>true, 'msg'=>$e->getMessage()]);
        }

        return response::json($exchangeInfo);
    }

}

