<?php

class syspromotion_ctl_admin_distribute_main extends desktop_controller {

    public function index()
    {
        return $this->finder('syspromotion_mdl_distribute', array(
                'actions'=>array(
                    array(
                        'label'=>app::get('syspromotion')->_('会员优惠定向发放'),
                        'target'=>'dialog::{ title:\''.app::get('syspromotion')->_('会员优惠定向发放').'\', width:1200, height:600}',
                        'href'=>'?app=syspromotion&ctl=admin_distribute_main&act=add',
                    ),
                ),
                'title' => app::get('syspromotion')->_('会员优惠定向发放'),
                'use_buildin_delete' => false,
            ));

    }

    public function add(){
        return $this->page('syspromotion/distribute/add.html');
    }

    public function doAdd(){

        try{
            $data = input::get();

            $this->begin("?app=syspromotion&ctl=admin_distribute_main&act=index");
            $distribute = $this->__postDataGenDistribute($data);

//          return $this->end(false, app::get('syspromotion')->_('保存成功'));

            kernel::single('syspromotion_distribute_object')->createDistribute($distribute);
            return $this->end(true, app::get('syspromotion')->_('保存成功'));
        }catch(Exception $e){

            logger::debug('Create Distribute Exception:'.$e->__toString());
            return $this->end(false, $e->getMessage());
        }

    }

    private function __postDataGenDistribute($data)
    {
        $distribute = [];
        $distribute['distribute_name'] = $data['distribute_name'];
        $distribute['user_filter']     = $data['user_filter'];
        $distribute['discount_type'] = $data['discount_type'];
        switch($data['discount_type'])
        {
        case 'hongbao':
            if(!$data['distribute']['hongbaoid'] || !$data['distribute']['hongbaomoney'])
                throw new LogicException(app::get('syspromotion')->_('请选取红包!'));
            $discount_param = [
                'hongbaoid'=>$data['distribute']['hongbaoid'],
                'hongbaomoney' => $data['distribute']['hongbaomoney'],
                'hongbao_obtain_type' => 'adminPut',
            ];
            break;

        case 'voucher':
            if(!$data['voucher_id'])
                throw new LogicException(app::get('syspromotion')->_('请选取购物券!'));
            $discount_param = [
                'voucher_id'=>$data['voucher_id'],
            ];
            break;

        default :
            throw new LogicException(app::get('syspromotion')->_('定向发放优惠类型不兼容'));
        }
        $distribute['discount_param'] = $discount_param;
        $distribute['remind_way']       = $data['remind_way'];
        $distribute['sms_tmpl']         = $data['sms_tmpl'];
        $distribute['email_tmpl']       = $data['email_tmpl'];

        return $distribute;
    }

}

