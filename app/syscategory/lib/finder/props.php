<?php
class syscategory_finder_props {

    public $column_edit = '编辑';
    public $column_edit_order = 1;
    public function column_edit(&$colList, $list){
        foreach($list as $k=>$row)
        {
            $html = '  <a href=\'?app=syscategory&ctl=admin_props&act=create&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['prop_id'].'\'" target="dialog::{title:\''.app::get('syscategory')->_('编辑').'\', width:640, height:420}">'.app::get('syscategory')->_('编辑').'</a> ';
            $objMdlProps = app::get('syscategory')->model('props');
            $propInfo = $objMdlProps->getRow("is_def",array('prop_id'=>$row['prop_id']));
            if($propInfo['is_def']==0)
                {
                   $delUrl = '?app=syscategory&ctl=admin_props&act=delPage&finder_id='.$_GET['_finder']['finder_id'].'&p[0]='.$row['prop_id'];
                   $target = 'target="dialog::{title:\''.app::get('syscategory')->_('提示').'\', width:250, height:150}"';
                    $html .= '| <a href="'.$delUrl.'" '.$target.'>'.app::get('syscategory')->_('删除').'</a>';
                }

            $colList[$k] = $html;
        }
    }
}

