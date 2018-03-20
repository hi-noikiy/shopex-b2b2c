<?php
class syspromotion_finder_activity_fullminus{

    public $column_edit = "操作";
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_edit(&$col ,$list)
    {
        foreach($list as $k=>$row)
        {
            if(app::get('sysconf')->getConf('shop.promotion.examine')){
                if ($row['fullminus_status'] =='pending') {
                    $title = app::get('syspromotion')->_('审核');
                    $url = url::route('shopadmin', ['app'=>'syspromotion','act'=>'index','ctl'=>'admin_fullminus','finder_id'=>$_GET['_finder']['finder_id'],'id'=>$row['fullminus_id'],'finderview'=>'detail_basic','action'=>'detail','singlepage'=>'true']);
                    $col[$k] = '<a href="'.$url.'" target="_blank" title="审核">审核</a>';
                }
            }
        }   
    }
    
    public $detail_basic = '促销信息';
    public function detail_basic($id)
    {
        $fullminusData = app::get('syspromotion')->model('fullminus')->getRow('*', array('fullminus_id'=>$id));
        // 获取活动规则信息
        $apiData = array(
            'fullminus_id' => $fullminusData['fullminus_id'],
            'fullminus_itemList' => true,
        );
        $pagedata = app::get('syspromotion')->rpcCall('promotion.fullminus.get', $apiData);
        $valid_grade = explode(',', $pagedata['valid_grade']);
        $pagedata['gradeList'] = app::get('topshop')->rpcCall('user.grade.list');
        $gradeIds = array_column($pagedata['gradeList'],'grade_id');
        if( !array_diff($gradeIds, $valid_grade))
        {
            $gradeStr = '所有会员';
        }
        else
        {
            foreach ($pagedata['gradeList'] as $member) {
                if(in_array($member['grade_id'],$valid_grade))
                {
                    $gradeStr .= $member['grade_name'].',';
                }
            }
            $gradeStr = rtrim($gradeStr,',');
        }
        $pagedata['grade_str'] = $gradeStr;
        $pagedata['condition_value'] = $this->condition($pagedata['condition_value']);
        $pagedata['examine_setting'] = app::get('sysconf')->getConf('shop.promotion.examine');

        $examineData= redis::scene('syspromotion')->lrange('fullminus_id_'.$id,0,-1);
        $pagedata['examineLog'] = array();
        foreach ($examineData as $key => $value) {
            $pagedata['examineLog'][$key] = unserialize($value);
        }

        return view::make('syspromotion/activity/fullminus/detail.html',$pagedata)->render();
    }

    public function condition($condition)
    {
        $condList = explode(',',$condition);
        foreach ($condList as $key => $value)
        {
            $condList[$key] = explode('|',$value);
        }
        return $condList;
    }
}
