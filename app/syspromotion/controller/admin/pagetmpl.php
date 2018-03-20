<?php

/**
 * pagetmpl.php
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class syspromotion_ctl_admin_pagetmpl extends desktop_controller {

    public $workground = 'site.wrokground.theme';
    public function index()
    {
        $target = 'dialog::{title:\''.app::get('syspromotion')->_('添加促销专题模板').'\', width:800, height:580}';
        $actions = [
            [
                'label' => app::get('syspromotion')->_('添加促销专题模板'),
                'href' => '?app=syspromotion&ctl=admin_pagetmpl&act=add',
                'target'=>$target,
            ],
            [
                'label' => app::get('syspromotion')->_('删除'),
                'icon' => 'download.gif',
                'submit' => url::route('shopadmin',['app'=>'syspromotion','act'=>'doDelete','ctl'=>'admin_pagetmpl']),
                'confirm' => app::get('syspromotion')->_('确定要删除选中的模板？'),
            ],
        ];

        return $this->finder('syspromotion_mdl_page_tmpl', [
                'use_buildin_filter' => false, 
                'use_buildin_refresh' => false, 
                'use_buildin_delete' => false, 
                'title' => app::get('syspromotion')->_('促销专题app模板列表'), 
                'actions' => $actions 
            ]
        );
    }

    // 添加专题页面
    public function add()
    {
        $ptmpl_id = input::get('ptmpl_id', 0);
        if(intval($ptmpl_id) > 0)
        {
            $data = kernel::single('syspromotion_pagetmpl')->getInfo($ptmpl_id);
        }

        $pagedata  = $data;
        return $this->page('syspromotion/admin/page/tmpl.html', $pagedata);
    }

    // 保存数据
    public function save()
    {
        $post = input::get();

        $this->begin("?app=syspromotion&ctl=admin_pagetmpl&act=index");
        $tmplMdl = app::get('syspromotion')->model('page_tmpl');
        $tmplInfo = $tmplMdl->getRow('ptmpl_id',array('ptmpl_name'=>$post['tmpl']['ptmpl_name']));

        if($tmplInfo && intval($post['tmpl']['ptmpl_id']) != $tmplInfo['ptmpl_id'])
        {
            $this->end(false,app::get('syspromotion')->_('模板名称不能重复！'));
        }
        else
        {
            try
            {
                kernel::single('syspromotion_pagetmpl')->saveData($post['tmpl']);
                $this->adminlog("保存促销专题模板{$postdata['ptmpl_name']}", 1);
                $this->end(true,app::get('syspromotion')->_('保存成功'));
            }
            catch (Exception $e)
            {
                $this->adminlog("保存促销专题模板{$postdata['ptmpl_name']}", 0);
                $msg = $e->getMessage();
                $this->end(false,$msg);
            }
        }
    }

    public function doDelete(){
        $ids= input::get('ptmpl_id');
        $this->begin("?app=syspromotion&ctl=admin_pagetmpl&act=index");
        try{
            kernel::single('syspromotion_pagetmpl')->delete($ids);
            $this->adminlog("删除app营销模板{$ids}",1);
            $this->end(true);
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            $this->adminlog("删除app营销模板{$ids}",0);
            $this->end(false,$msg);
        }
    }
}
 