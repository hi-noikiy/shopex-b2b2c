<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class sysapp_ctl_admin_push_clients extends desktop_controller {

    public function index(){
        return $this->finder('sysapp_mdl_clients',array(
            'title' => app::get('sysapp')->_('设备列表'),
            'use_buildin_filter' => true,
            'use_buildin_delete' => true,
            'actions'=>array(
                array(
                    'label'=>app::get('sysapp')->_('个推配置'),
                    'href' => '?app=sysapp&ctl=admin_push_igexin&act=config',
                    'target'=>'dialog::{title:\''.app::get('sysuser')->_('个推配置').'\',width:500,height:400}'
                ),
                array(
                    'label'=>app::get('sysapp')->_('发送全体通知'),
                    'href' => '?app=sysapp&ctl=admin_push_clients&act=pushAll',
                    'target'=>'dialog::{title:\''.app::get('sysuser')->_('发送全体通知').'\',width:500,height:400}'
                ),
            ),
        ));

    }

    public function pushAll()
    {

        return view::make('sysapp/admin/push/pushAll.html',$pagedata);
    }

    public function pushAllDo()
    {
        $this->begin();
        $data = input::get();
        $title = $data['title'];
        $message = $data['message'];
        $withParams = $data['withParams'];
        $params = $data['params'];

        try{
            if($withParams == 'true')
            {
                $appmap = kernel::single('sysapp_module_util')->processAppMapParams($params['linktype'], $params['link']);
                $params['webview'] = $appmap['webview'];
                $params['webparam'] = $appmap['webparam'];
                kernel::single('sysapp_push_object')->pushAllWithParams($title, $message, $params);
            }
            else
            {
                kernel::single('sysapp_push_object')->pushAll($title, $message);
            }
        }catch(Exception $e){
            logger::error('推送通知失败：' . $e->getMessage());
            return $this->end(false, app::get('sysapp')->_('发送消息失败，请查看日志'));
        }
        return $this->end(true, app::get('sysapp')->_('发送成功'));
    }


}



