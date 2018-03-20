<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topc_ctl_member_voucher extends topc_ctl_member {

    public function index()
    {
        $pageSize = 12;

        //获取购物券列表参数
        $apiParams['user_id'] = userAuth::id();
        $apiParams['page_no'] = input::get('pages',1);
        $apiParams['page_size'] = $pageSize;
        $apiParams['fields'] = '*';
        $status = input::get('status','1');
        if( in_array($status, ['0', '1', '2']) )
        {
            $apiParams['is_valid'] = $status;
        }

        $voucherData = app::get('topc')->rpcCall('user.voucher.list.get', $apiParams);

        if( $voucherData['list'] )
        {
            foreach( $voucherData['list'] as &$row )
            {
                $row['used_platform'] = explode(',',$row['used_platform']);
            }
        }

        $pagedata['total'] = $voucherData['pagers']['total'];
        $pagedata['list']  = $voucherData['list'];

        //处理翻页数据
        $current = $apiParams['page_no'] ? $apiParams['page_no'] : 1;
        $filter['status'] = $status;
        $filter['pages'] = time();
        if($pagedata['total']>0) $total = ceil($pagedata['total']/$pageSize);
        $pagedata['pagers'] = array(
            'link'=>url::action('topc_ctl_member_voucher@index',$filter),
            'current'=>$current,
            'total'=>$total,
            'token'=>$filter['pages'],
        );

        $pagedata['now'] = time();
        $pagedata['status'] = $status;

        $this->action_view = "voucher/list.html";
        return $this->output($pagedata);
    }

    public function getVoucher()
    {
        $userId = userAuth::id();
        $voucherId = input::get('voucher_id');
        try
        {
            if(app::get('topc')->rpcCall('user.voucher.code.get', ['voucher_id'=>$voucherId,'user_id'=>$userId]))
            {
                $url = url::action('topc_ctl_member_voucher@index');
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
}
