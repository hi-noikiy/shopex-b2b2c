<?php
class sysuser_mdl_user_grade extends dbeav_model{

    public function delete($filter,$subSdf = 'delete')
    {
        $info = $this->getList('grade_id,grade_name,default_grade',$filter);
        $userMdl = app::get('sysuser')->model('user');
        foreach($info as $value)
        {
            if($value['default_grade'] == 1)
            {
                $this->delete_msg = "[".$value['grade_name']."]".app::get('sysuser')->_('是系统默认会员等级，不可删除！');
                return false;
            }

            //查看该等级下是否有会员
            $count = $userMdl->count(['grade_id'=>$value['grade_id']]);
            if($count > 0)
            {
                $this->delete_msg = "[".$value['grade_name']."]".app::get('sysuser')->_('下有会员，不可删除！');
                return false;
            }

        }
        return parent::delete($filter);
    }
}
