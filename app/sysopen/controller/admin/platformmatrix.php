<?php

class sysopen_ctl_admin_platformmatrix extends desktop_controller {

    public function index()
    {
        return $this->finder('sysopen_mdl_platform_bind',array(
            'use_buildin_filter'=>false,
            'use_view_tab'=>true,
            'title' => app::get('systrade')->_('平台与shopex体系内产品绑定列表'),
            'use_buildin_delete'=>true,
            'actions' => array(
                array(
                    'label'=>app::get('b2c')->_('查看绑定情况'),
                    'icon'=>'add.gif',
                    'onclick'=>'new Request({evalScripts:true,url:\'?app=sysopen&ctl=admin_platformmatrix&act=showBind\'}).get()'
                ),

                array(
                    'label'=>app::get('sysopen')->_('添加绑定节点'),
                    'href'=>'?app=sysopen&ctl=admin_platformmatrix&act=bind',
                    'target'=>'dialog::{title:\''.app::get('sysopen')->_('添加绑定节点').'\',width:300,height:300}'
                ),

                array(
                    'label'=>app::get('b2c')->_('推送全部会员数据到matrix'),
                    'icon'=>'add.gif',
                    'onclick'=>'if(confirm(\'是否要同步全部会员到矩阵？\')){new Request({evalScripts:true,url:\'?app=sysuser&ctl=admin_user&act=pushAllUserToMatrix\'}).get();}'
                ),

              //array(
              //    'label'=>app::get('sysopen')->_('清除节点信息'),
              //    'href'=>'?app=sysopen&ctl=admin_platformmatrix&act=cleanNode',
              //),

            )
        ));
    }

    public function showBind(){

        $url = kernel::single('sysopen_shopex_bind')->showBind();
        $html = "<script>new Dialog('$url',{iframe:true,title:'BBC平台绑定关系',width:.8,height:.8})</script>";
        return $html;
    }

    public function cleanNode(){
        $this->begin("?app=sysopen&ctl=admin_platformmatrix&act=index");
        sysopen_shopexnode::deleteNodeInfo();
        return $this->end(true);
    }

    public function bind(){
        $shopexNod = sysopen_shopexnode::getNodeInfo();
        $pagedata['node_id'] = $shopexNod['node_id'];

        return $this->page('sysopen/platformmatrix/bindPage.html', $pagedata);
    }

    public function dobind(){
        $this->begin("?app=sysopen&ctl=admin_platformmatrix&act=index");

        $data = input::get();
        $params['title']     = $data['node_title'];
        $params['node_id']   = $data['to_node_id'];
        $params['node_type'] = $data['node_type'];

        try{
            if(kernel::single('sysopen_shopex_bind')->applyBind($params))
            {
                return $this->end(true);
            }else{
                return $this->end(false);
            }
        }catch(Exception $e){
            return $this->end(false, $e->getMessage(), '');
        }
    }

}
