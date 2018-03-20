<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class sysconf_ctl_admin_setting extends desktop_controller{
    public $require_super_op = true;
    public function __construct($app)
    {
        parent::__construct($app);
        $this->app = app::get('sysconf');//$app;
    }

    public function index()
    {
        $post = $_POST;
        $typemap = array(
            SET_T_STR=>'text',
            SET_T_INT=>'number',
            SET_T_ENUM=>'select',
            SET_T_BOOL=>'bool',
            SET_T_TXT=>'text',
            SET_T_FILE=>'file',
            SET_T_IMAGE=>'image',
            SET_T_DIGITS=>'number',
        );

        $all_settings = config::get('sysconf');
        $tabs = array_keys($all_settings);
        $html = view::ui()->form_start(array('tabs'=>$tabs,'method'=>'POST','id'=>'setting_form'));
        $input_style = false;
        $arrJs = array();
        foreach($tabs as $tab=>$settings)
        {
            foreach($all_settings[$settings] as $set=>$setting)
            {
                $currentSet = $pre_set = app::get('sysconf')->getConf($set);
                if($post['set'] && array_key_exists($set,$post['set']))
                {
                    if($post['set']['open.checkin'] ==1 && $post['set']['open.point'] ==0)
                    {
                        return $this->splash('error',null,'开启签到情况下，必须选择一种签到活动');
                    }

                    if($post['set']['point.expired.month'] >12 || $post['set']['point.expired.month'] <1)
                    {
                        return $this->splash('error',null,'“积分过期月份”必须是大于等于1小于等于12的正整数');
                    }

                    if($post['set']['point.expired.month'] >12 || $post['set']['point.expired.month'] <1)
                    {
                        return $this->splash('error',null,'“积分过期月份”必须是大于等于1小于等于12的正整数');
                    }

                    if($post['set']['open.point.deduction'] && ($post['set']['point.deduction.max'] > 99 || $post['set']['point.deduction.max'] < 1))
                    {
                        return $this->splash('error',null,'“积分抵扣上限”必须是大于等于[订单总额的1%]小于等于[订单总额的99%]的正整数');
                    }

                    if($currentSet !== $post['set'][$set])
                    {
                        $currentSet = $post['set'][$set];
                        app::get('sysconf')->setConf($set,$currentSet);
                    }
                }
                $inputType = $typemap[$setting['type']];
                $formInput = array(
                    'title' => $setting['desc'],
                    'type' =>$typemap[$setting['type']],
                    'name' => "set[".$set."]",
                    'tab' => $tab,
                    'helpinfo'=>$setting['helpinfo'],
                    'value' => $currentSet ? $currentSet : $setting['default'],
                    'options'=>$setting['options'],
                    'vtype'=>$setting['vtype'],
                    'class' => $setting['class'],
                    'id' => $setting['id'],
                    'default' => $setting['default'],
                );
                if($inputType == "select")
                {
                    $formInput['required'] = true;
                }
                if(isset($setting['extends_attr'])&&$setting['extends_attr']&&is_array($setting['extends_attr']))
                {
                    foreach($setting['extends_attr'] as $key=>$extends_attr)
                    {
                        $formInput[$key] = $extends_attr;
                    }
                }
                $arrJs[] = $setting['javascript'];
                $html .= view::ui()->form_input($formInput);
            }
        }

        if(!$post)
        {
            $html .=  view::ui()->form_end();
            $strJs = '<script type="text/javascript">window.addEvent(\'domready\',function(){';
            if (is_array($arrJs) && $arrJs)
            {
                foreach ($arrJs as $strJavascript)
                {
                    $strJs .= $strJavascript;
                }
            }
            $strJs .= '$("main").addEvent("click",function(el){
                el = el.target || el;
                if ($(el).get("id")){
                    var _id = $(el).get("id");
                    var _class_name = "";
                    if (_id.indexOf("-t") > -1){
                        _class_name = _id.substr(0, _id.indexOf("-t"));
                        $$("."+_class_name).getParent("tr").show();
                     }
                if (_id.indexOf("-f") > -1){
                    _class_name = _id.substr(0, _id.indexOf("-f"));
                    var _destination_node = $$("."+_class_name);
                    _destination_node.getParent("tr").hide();
                    _destination_node.each(function(item){if (item.getNext(".caution") && item.getNext(".caution").hasClass("error")) item.getNext(".caution").remove();});
                }
                }
            });';

            $strJs .= '});</script>';
            $pagedata['_PAGE_CONTENT'] .= $html.$strJs;
            return $this->page(null, $pagedata);
        }
        else
        {
            $this->begin();
            if(!app::get('sysconf')->getConf('shop.goods.examine')){
                event::fire('item.updateStatus');
            }
            if(!app::get('sysconf')->getConf('shop.promotion.examine')){
                event::fire('promotion.updateStatus');
            }

            return $this->end(true, app::get('sysconf')->_('当前配置修改成功！'));
        }
    }
    //移动端配置
    public function wapSet()
    {
        $url = "http://images.bbc.shopex123.com/images/33/e2/ff/56e438276be7f2d7ae2b7bede423048f6847e906.png";
        $pagedata['wap_logo']  = app::get('sysconf')->getConf('sysconf_setting.wap_logo') ? app::get('sysconf')->getConf('sysconf_setting.wap_logo') : $url;
        $pagedata['wapmac_logo']  = app::get('sysconf')->getConf('sysconf_setting.wapmac_logo');
        $pagedata['wap_name']  = app::get('sysconf')->getConf('sysconf_setting.wap_name');
        $pagedata['wap_license']  = app::get('sysconf')->getConf('sysconf_setting.wap_license');
        $pagedata['wap_description'] = app::get('sysconf')->getConf('sysconf_setting.wap_description');
        $pagedata['wap_usage_agreement']  = app::get('sysconf')->getConf('sysconf_setting.wap_usage_agreement');
        return $this->page('sysconf/wapsetting.html',$pagedata);
    }

    //移动端配置
    public function saveSet()
    {
        $this->begin();
            app::get('sysconf')->setConf('sysconf_setting.wap_logo',$_POST['wap_logo']);
            app::get('sysconf')->setConf('sysconf_setting.wapmac_logo',$_POST['wapmac_logo']);
            app::get('sysconf')->setConf('sysconf_setting.wap_name',$_POST['wap_name']);
            app::get('sysconf')->setConf('sysconf_setting.wap_license',$_POST['wap_license']);
            app::get('sysconf')->setConf('sysconf_setting.wap_description',$_POST['wap_description']);
            app::get('sysconf')->setConf('sysconf_setting.wap_usage_agreement',$_POST['wap_usage_agreement']);
        $this->adminlog("编辑移动端配置", 1);
        $this->end(true,app::get('sysconf')->_('保存成功'));
    }

    public function shopSet()
    {
        $pagedata['shop_login_background'] = app::get('sysconf')->getConf('sysconf_setting.shop_login_background');
        $pagedata['shop_login_background'] = $pagedata['shop_login_background'] ?: app::get('topshop')->res_url . '/images/bj_01.jpg';
        return $this->page('sysconf/shopsetting.html',$pagedata);
    }


    public function shopSaveSet()
    {
        $this->begin();
        app::get('sysconf')->setConf('sysconf_setting.shop_login_background',$_POST['shop_login_background']);
        $this->end(true,app::get('sysconf')->_('保存成功'));
    }
}
