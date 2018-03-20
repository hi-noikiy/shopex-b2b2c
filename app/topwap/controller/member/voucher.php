<?php

class topwap_ctl_member_voucher extends topwap_ctl_member{
	public function index()
    {

        $filter = input::get();
        $pagedata = $this->_getVoucherList($filter);
        $pagedata['title'] = app::get('topwap')->_('我的购物券');
        return $this->page('topwap/member/voucher/index.html', $pagedata);
    }

    public function getVoucher()
    {
        $userId = userAuth::id();
        $voucherId = input::get('voucher_id');
        try
        {
            if(app::get('topwap')->rpcCall('user.voucher.code.get', ['voucher_id'=>$voucherId,'user_id'=>$userId]))
            {
                $url = url::action('topwap_ctl_member_voucher@index');
                return $this->splash('success', null, '领取成功', true);
            }
            else
            {
                return $this->splash('error', '', '领取失败', true);
            }
        }
        catch(\LogicException $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', '', $msg, true);
        }
        catch( Exception $e )
        {
            return $this->splash('error', '', '领取失败', true);
        }
    }

    private function _getVoucherList($filter)
    {
        if(!$filter['pages'])
        {
             $filter['pages'] = 1;
        }
        $pageSize = $this->limit;

        //获取购物券列表参数
        $apiParams['user_id'] = userAuth::id();
        $apiParams['page_no'] = $filter['pages'];
        $apiParams['page_size'] = $pageSize;
        $apiParams['fields'] = '*';

        $filter['is_valid']=isset($filter['is_valid'])?$filter['is_valid']:'no';
        $filter['is_valid'] = $this->__getValid($filter);
        $apiParams['is_valid'] = $filter['is_valid'];

        $voucherData = app::get('topwap')->rpcCall('user.voucher.list.get', $apiParams);

        if( $voucherData['list'] )
        {
            foreach( $voucherData['list'] as &$row )
            {
                $row['used_platform'] = explode(',',$row['used_platform']);
            }
        }

        $pagedata['count'] = $count = $voucherData['pagers']['total'];
        $pagedata['list']  = $voucherData['list'];

        //处理翻页数据
        $current = $apiParams['page_no'];
        $pagedata['pages'] = $current;
        if($count>0) $total = ceil($count/$pageSize);
        $pagedata['pagers'] = array(
                'link'=>'',
                'current'=>$current,
                'total'=>$total,
                'is_valid'=>input::get('is_valid')?input::get('is_valid'):'no',
        );

        $pagedata['now'] = time();
        return $pagedata;
    }

    public function ajaxVoucherList()
    {
        try {
            $filter = input::get();
            $pagedata = $this->_getVoucherList($filter);
            if($pagedata['list']){
                $data['html'] = view::make('topwap/member/voucher/list.html',$pagedata)->render();
            }else{
                $data['html'] = view::make('topwap/empty/voucher.html',$pagedata)->render();
            }

            $data['pages'] = $pagedata['pages'];
            $data['pagers'] = $pagedata['pagers'];
            $data['is_valid'] = $filter['is_valid'];
            $data['success'] = true;
        } catch (Exception $e) {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }

        return response::json($data);exit;
    }

    private function __getValid($filter)
    {
        switch ($filter['is_valid']) {
            case '0':
                $filter['is_valid']=1;
                break;
            case '1':
                $filter['is_valid']=0;
                break;
            case '2':
                $filter['is_valid']=2;
                break;
            default:
                $filter['is_valid']=1;
                break;
        }
        return $filter['is_valid'];
    }
}
