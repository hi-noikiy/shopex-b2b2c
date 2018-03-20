<?php

class syspromotion_finder_scratchcard {

    public $column_edit = "编辑";
    public $column_edit_order = 1;
    public $column_edit_width = 10;

    public function column_edit(&$colList,$list)
    {
        foreach($list as $k=>$row)
        {
            $editUrl = url::route('shopadmin', ['app'=>'syspromotion','act'=>'edit','ctl'=>'admin_scratchcard','_finder[finder_id]'=>$_GET['_finder']['finder_id'],'scratchcard_id'=>$row['scratchcard_id']]);
            $editTitle = '编辑';
            $editTarget = 'dialog::{ title:\''.app::get('sysuser')->_('编辑刮刮卡信息').'\', width:1250, height:600}';
          //$logTitle = '查看获奖明细';
          //$logTarget = '_blank';
          //$logUrl = '?app=desktop&amp;act=alertpages&amp;goto=%3Fapp%3Dsyspromotion%26ctl%3Dadmin_scratchcard%26act%3DlogDetail%26scratchcard_id%3D'.$row['scratchcard_id'].'%26nobuttion%3D1';

          //$colList[$k] = '<a href="' . $editUrl . '" target="' . $editTarget . '">' . $editTitle . '</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $logUrl . '" target="' . $logTarget . '">' . $logTitle . '</a>';
            $colList[$k] = '<a href="' . $editUrl . '" target="' . $editTarget . '">' . $editTitle . '</a> ';
            if($row['used_platform'] !=2) {
              $colList[$k] = $colList[$k] .
                ' <a href="'.url::action('topwap_ctl_promotion_scratchcard@index', ['scratchcard_id'=>$row['scratchcard_id']]).'" target="_blank" >查看页面</a>';
            }
            
        }
    }

  //public $column_preview = "预览";
  //public $column_preview_order = 2;
  //public $column_preview_width = 10;
  //public function column_preview(&$colList,$list)
  //{
  //    foreach ($list as $key => $value) {
  //        $url = url::action('topc_ctl_scratchcard@index', ['scratchcard_id'=>$value['scratchcard_id']]);
  //        $colList[$key] = '<a href="' . $url . '" target="_blank" title="只有开启活动后，才可进行预览"> pc预览 </a>';
  //    }
  //}
}
