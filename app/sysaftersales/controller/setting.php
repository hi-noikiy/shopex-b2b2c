<?php

class sysaftersales_ctl_setting extends desktop_controller {

    public $workground = 'sysaftersales.workground.aftersale';

    public function index(){
        $refundSetting = app::get('sysaftersales')->getConf('refundSetting');
        $changingSetting = app::get('sysaftersales')->getConf('changingSetting');
        $pagedata['refundSetting'] = unserialize($refundSetting);
        $pagedata['changingSetting'] = unserialize($changingSetting);

        return $this->page('sysaftersales/aftersalesSetting.html', $pagedata);
    }

    public function save(){
        $aftersales = input::get('aftersales');
        try{
            if($aftersales['day'] < 0 || strpos($aftersales['day'], '.') || !is_numeric($aftersales['day'])){
                throw new \LogicException(app::get('sysaftersales')->_('退换货时间限定请输入大于等于0的整数！'));
            }
            if($aftersales['type'] =='refund'){
                app::get('sysaftersales')->setConf('refundSetting', serialize($aftersales));
            }elseif ($aftersales['type'] == 'changing'){
                app::get('sysaftersales')->setConf('changingSetting', serialize($aftersales));
            }
        }
        catch(Exception $e)
        {
            $msg = $e->getMessage();
            return $this->splash('error', null, $msg, 'ture');
        }

        $msg = app::get('sysaftersales')->_('设置成功！');
        return $this->splash('success',null,$msg,'true');
    }

    public function getCatList(){
        $aftersalestype = input::get('aftersalesType');
        $selectedCat =  app::get('sysaftersales')->rpcCall('aftersales.cat.setting.list',['aftersalestype'=>$aftersalestype]);
        $categorylist = app::get('syscategory')->rpcCall('category.cat.get.list');

        foreach( $categorylist as $row )
        {
            if( $row['level'] == 1 )
            {
                if($row['lv2']) unset($row['lv2']);
                $selectNode[] = $row;
            }
        }
        $pagedata['aftersaletype'] = $aftersalestype;
        $pagedata['selectNode'] = $selectNode;
        $pagedata['catSetting'] = $selectedCat;

       return $this->page('sysaftersales/catSetting.html', $pagedata);
    }

    public function saveCatSetting(){
        $params = input::get('aftersales');
        $objMdlAftersales = app::get('sysaftersales')->model('setting');
        $this->begin();
        if($params['type'] == 'changing'){
            $objMdlAftersales->delete(['refund_days|nequal'=>-1]);
            $objMdlAftersales->update(['changing_days'=>-1],['refund_days|noequal'=>-1]);
        }
        elseif($params['type'] == 'refund') {
            $objMdlAftersales->delete(['changing_days|nequal'=>-1]);
            $objMdlAftersales->update(['refund_days'=>-1],['changing_days|noequal'=>-1]);
        }

        foreach ($params['cat'] as $catId => $value) {
            $objMdlAftersales->save($value);
        }

        $this->end(true);
    }
}