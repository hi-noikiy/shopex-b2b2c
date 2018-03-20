<?php
class sysuser_api_grade_basicinfo{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取会员当前等级基本信息';

    /**
     * 定义应用级参数，参数的数据类型，参数是否必填，参数的描述
     * 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入
     * user.grade.basicinfo
     */
    public function getParams()
    {
        //接口传入的参数
        $return['params'] = array(
            'user_id' => ['type'=>'int', 'valid'=>'', 'title'=>'用户id', 'example'=>'', 'desc'=>'用户id'],
        );

        return $return;
    }

    public function basicinfo($params)
    {
        $objMdlUser = app::get('sysuser')->model('user');
        $objMdlUserGrade = app::get('sysuser')->model('user_grade');
        if($params['user_id'])
        {
            $userId = $params['user_id'];
        }
        else
        {
            $userId = pamAccount::getAccountId();
        }
        $userData = $objMdlUser->getRow('grade_id,experience', array('user_id'=>$userId));
        $gradeData = $objMdlUserGrade->getRow("grade_name,grade_logo", array('grade_id'=>$userData['grade_id']));

        $rsdata = array_merge($userData, $gradeData);
        return $rsdata;
    }
}

