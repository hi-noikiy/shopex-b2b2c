<?php

class topwap_ctl_member_hongbao extends topwap_ctl_member {

    public $limit = 10;

    public function index()
    {
        $pagedata = $this->_getCouponList();
        $pagedata['title'] = app::get('topwap')->_('我的红包');

        return $this->page('topwap/member/hongbao/index.html', $pagedata);
    }


    public function ajaxHongbaoList()
    {
        try
        {
            $pagedata = $this->_getCouponList();
            if($pagedata['list'])
            {
                $data['html'] = view::make('topwap/member/hongbao/list.html',$pagedata)->render();
            }
            else
            {
                $data['html'] = view::make('topwap/empty/hongbao.html',$pagedata)->render();
            }

            $data['pages'] = $pagedata['pages'];
            $data['pagers'] = $pagedata['pagers'];
            $data['is_valid'] = $pagedata['is_valid'];
            $data['success'] = true;
        }
        catch (Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, true);
        }

        return response::json($data);
    }

    protected function _getCouponList()
    {
        $isValid = input::get('is_valid','active');
        if( $isValid == '0' )
        {
            $isValid = 'active';
        }
        elseif($isValid == '1')
        {
            $isValid = 'used';
        }
        elseif($isValid == '2')
        {
            $isValid = 'expired';
        }

        $apiParams = [
            'user_id' => userAuth::id(),
            'is_valid'=> $isValid,
            'page_no' => input::get('pages',1),
            'page_size' => $this->limit,
            'fields'=>'*'
        ];
        $hongbaoData = app::get('topwap')->rpcCall('user.hongbao.list.get', $apiParams);

        if($hongbaoData['pagers']['total']>0) $total = ceil($hongbaoData['pagers']['total']/$this->limit);
        $pagedata['list'] = $hongbaoData['list'];

        $current = intval(input::get('pages',1));
        $current = $total < $current ? $total : $current;

        $pagedata['count'] = $hongbaoData['pagers']['total'];
        $pagedata['pages'] = $current;
        $pagedata['pagers'] = array(
            'link'=>'',
            'current'=>$current,
            'total'=>$total,
            'is_valid'=>input::get('is_valid','0')
        );
        $pagedata['is_valid'] = $isValid;
        return $pagedata;
    }

    public function getHongbao()
    {
        $apiParams = [
            'user_id' => userAuth::id(),
                'hongbao_id' => input::get('hongbao_id'),
                'money' => input::get('money'),
                'hongbao_obtain_type' => 'userGet',
        ];

        try
        {
            $hongbaoData = app::get('topwap')->rpcCall('user.hongbao.get', $apiParams);
        }
        catch( LogicException $e )
        {
            $msg = $e->getMessage();
            return $this->splash('error',"",$msg,true);
        }
        catch( Exception $e)
        {
            $msg = '红包已领完';
            return $this->splash('error',"",$msg,true);
        }

        return $this->splash('success',"",'红包领取成功',true);
    }
}

