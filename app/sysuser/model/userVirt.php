<?php

class sysuser_mdl_userVirt extends sysuser_mdl_user
{

    function __construct($app){
        parent::__construct($app);
        $this->__resetDbschema();
    }

    public function table_name($real=false){
        if($real){
            return 'sysuser_user';
        }else{
            return 'user';
        }
    }



    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderBy=null)
    {
        $list = parent::getList($cols, $filter, $offset, $limit, $orderBy);
            if(count($list) == 0){
                return [];
            }
        $list = $this->__injectUserName($list);

        return $list;
    }

    private function __injectUserName($list)
    {
        $ids = array_column($list, 'user_id');
        $accounts = app::get('sysuser')->model('account')->getList('*', ['user_id|in'=>$ids]);
        $accounts = array_bind_key($accounts, 'user_id');

        foreach ($list as $key => $user)
        {
            $uid = $user['user_id'];
            $list[$key]['login_account'] = $accounts[$uid]['login_account'];
            $list[$key]['mobile']        = $accounts[$uid]['mobile'];
            $list[$key]['email']         = $accounts[$uid]['email'];
            $list[$key]['displayinfo']   = '用户名:' . $accounts[$uid]['login_account'] . ';  手机号:' . $accounts[$uid]['mobile'] . ';  邮箱:' . $accounts[$uid]['email'];
        }

        return $list;
    }

    private function __resetDbschema(){
        $schema = $this->schema;
        $schema['columns']['login_account'] = [
            'type' => 'string',
            'label' => '用户名',
            'in_list' => true,
            'default_in_list' => true,
            'doctrineType' => [ 'string', ['notnull'=>false, 'length'=>50] ],
        ];
        $schema['columns']['mobile'] = [
            'type' => 'string',
            'label' => '手机号',
            'in_list' => true,
            'default_in_list' => true,
            'doctrineType' => [ 'string', ['notnull'=>false, 'length'=>50] ],
        ];
        $schema['columns']['email'] = [
            'type' => 'string',
            'label' => '电子邮箱',
            'in_list' => true,
            'default_in_list' => true,
            'doctrineType' => [ 'string', ['notnull'=>false, 'length'=>50] ],
        ];
        $schema['in_list'][] = 'login_account';
        $schema['in_list'][] = 'mobile';
        $schema['in_list'][] = 'email';
        $schema['default_in_list'][] = 'login_account';
        $schema['default_in_list'][] = 'mobile';
        $schema['default_in_list'][] = 'email';


        $this->schema = $schema;


    }
}

