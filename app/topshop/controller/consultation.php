<?php
class topshop_ctl_consultation extends topshop_controller{
    public $limit = 10;

    public function index()
    {
        $this->contentHeaderTitle = app::get('topshop')->_('咨询管理-咨询列表');
        $pagedata = $this->__searchData();
        return $this->page('topshop/consultation/list.html', $pagedata);
    }

    public function screening()
    {
        $pagedata = $this->__searchData();
        return view::make('topshop/consultation/list_item.html', $pagedata);
    }

    private function __searchData()
    {
        $current = input::get('pages',1);
        $params = ['shop_id'=>$this->shopId,'page_no'=>intval($current),'page_size'=>intval($this->limit),'fields'=>'*'];

        $filter = input::get();
        if( isset($filter['type']) && $filter['type'] )
        {
            $params['type'] = input::get('type');
        }

        //咨询筛选时 全部标签下根据条件筛选不到信息
        if( !isset($filter['is_reply']) || $filter['is_reply'] == 'all' )
        {
            $filter['is_reply'] = 'all';
        }
        else
        {
            $params['is_reply'] = input::get('is_reply');
        }

        $data = app::get('topshop')->rpcCall('rate.gask.list', $params,'seller');
        $pagedata['gask']= $data['lists'];

        $pagedata['filter'] = $filter;
        $pagedata['total'] = $data['total_results'];

        //处理翻页数据
        $filter['pages'] = time();
        if($data['total_results']>0) $total = ceil($data['total_results']/$this->limit);
        $current = $total < $current ? $total : $current;
        $pagedata['pagers'] = array(
            'link'=>url::action('topshop_ctl_consultation@index',$filter),
            'current'=>$current,
            'use_app' => 'topshop',
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        return $pagedata;
    }

    public function doDelete()
    {
        $id = input::get('id');
        try{
            $flag = app::get('topshop')->rpcCall('rate.gask.delete',array('id' =>$id,'shop_id' => $this->shopId));
            $status = $flag ? 'success' : 'error';
            $msg = $flag ? app::get('topshop')->_('删除回复成功') : app::get('topshop')->_('删除回复失败');
        }
        catch(Exception $e)
        {
            $status = 'error';
            $msg = $e->getMessage();
        }
        $this->sellerlog('删除咨询回复。');
        $url = url::action('topshop_ctl_consultation@index');
        return $this->splash($status,$url,$msg,true);
    }

    public function doReply()
    {
        if(!input::get('id'))
        {
            return $this->splash('error','','要回复的咨询id为空',true);
        }
        if(!input::get('content'))
        {
            return $this->splash('error','','要回复的内容为空',true);
        }
        $params['id'] = intval(input::get('id'));
        $params['content'] = input::get('content');
        $params['author_id'] = $this->sellerId;
        $params['author'] = $this->sellerName;
        $params['shop_id'] = $this->shopId;
        try
        {
            $flag = app::get('topshop')->rpcCall('rate.gask.reply',$params);
            $status = $flag ? 'success' : 'error';
            $msg = $flag ? app::get('topshop')->_('回复成功') : app::get('topshop')->_('回复失败');
        }
        catch(Exception $e)
        {
            $status = 'error';
            $msg = $e->getMessage();
        }
        $this->sellerlog('回复咨询。');
        $url = url::action('topshop_ctl_consultation@index');
        return $this->splash($status,$url,$msg,true);
    }

    public function doDisplay()
    {
        $id = intval(input::get('id'));
        $status = input::get('status');
        try{
            $flag = app::get('topshop')->rpcCall('rate.gask.display',array('id' =>$id,'shop_id' => $this->shopId,'display' => $status));
            $status = $flag ? 'success' : 'error';
            $msg = $flag ? app::get('topshop')->_('操作成功') : app::get('topshop')->_('操作失败');
        }
        catch(Exception $e)
        {
            $status = 'error';
            $msg = $e->getMessage();
        }
        $this->sellerlog('更新咨询、回复显示状态。');
        $url = url::action('topshop_ctl_consultation@index');
        return $this->splash($status,$url,$msg,true);
    }
}
