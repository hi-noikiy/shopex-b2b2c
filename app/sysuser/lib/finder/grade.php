<?php
class sysuser_finder_grade{

    public $column_edit = "编辑";
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            $url = '?app=sysuser&ctl=admin_grade&act=create&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['grade_id'];
            $target = 'dialog::  {title:\''.app::get('sysuser')->_('会员等级编辑').'\', width:500, height:400}';
            $title = app::get('sysuser')->_('编辑');
            $button = '<a href="' . $url . '" target="' . $target . '">' . $title . '</a>';

            /*
              if(!$row['default_grade'])
              {
              $url = '?app=sysuser&ctl=admin_grade&act=create&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['grade_id'];
              $target = 'dialog::  {title:\''.app::get('sysuser')->_('会员等级编辑').'\', width:500, height:400}';
              $title = app::get('sysuser')->_('删除');
              $button .= ' |  <a href="' . $url . '" target="' . $target . '">' . $title . '</a>';
              }
            */
            $colList[$k] = $button;
        }
    }

    public $column_grade_logo = "等级LOGO";
    public $column_grade_logo_order = 2;
    public function column_grade_logo(&$colList, $list)
    {
        foreach($list as $k=>$row)
        {
            if($row['grade_logo']){
                $src = base_storager::modifier($row['grade_logo']);
                $colList[$k] = "<a href='$src' class='img-tip pointer' target='_blank' onmouseover='bindFinderColTip(event);'><span><i class='fa fa-picture-o'></i></span></a>";
            }
        }
    }

}
