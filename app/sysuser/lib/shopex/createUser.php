<?php
/**
 * 这里其实就是一个Transformer，用来把数据转化成举证要的格式
 */
class sysuser_shopex_createUser
{

    public $apiMethod = 'store.user.add';

    //网站上的文档有错误，所以这里放了数据结构
    public $userStruct = [
        'uid' => null,              //string 会员id
        'user_name' => null,        //string 用户名（和手机号有一个必填）
        'nick_name'=>null,          //string 昵称
        'sex' => null,              //number, 性别 1男，2女
        'buyer_credit' => null,     //UserCredit 买家信用
        'seller_credit' => null,    //UserCredit 卖家信用
        'location'=>null,           //string 地址
        'created'=>null,            //date 2000-01-01 00:00:00 注册时间
        'last_visit'=>null,         //date 2000-01-01 00:00:00 最后登录时间
        'birthday'=>null,           //date 2000-01-01 00:00:00 最后登录时间
        'birthday'=>null,           //date 2000-01-01 00:00:00 最后登录时间
        'promoted_type' => null,    //string 有无实名认证。可选值:authentication(实名认证),not authentication(没有认证)
        'status' => null,           //string 状态。可选值:normal(正常),inactive(未激活),delete(删除),reeze(冻结),supervise(监管)
        'alipay_bind' => null,      //string 有无绑定。可选值:bind(绑定),notbind(未绑定)此为绑定支付宝帐号标识
        'consumer_protection'=>null,//boolean 是否参加消保
        'alipay_account' => null,   //string 支付宝账号
        'alipay_no' => null,        //string 支付宝id
        'vip_info'=> null,          //string 用户的全站vip信息(VIP信息可以自定义)
        'email' => null,            //string email
        'marital' => null,          //number 婚姻状态，1-已婚；2-未婚
        'mobile' => null,           //string 手机号
        'age' => null,              //number 年龄
    ];

    //返回shopex体系创建会员结构
    public function handle($params)
    {
        $userId = $params['user_id'];
        $userData = $this->__getUser($userId);
        $postUser = $this->__genUser($userData);
        logger::debug('push matrix user data : '.var_export($postUser, 1));
        return $postUser;
    }

    /**
     * @brief 获取会员信息
     * @param userId
     *
     * @return 'userId' => 1,
     * @return 'addr' => NULL,
     * @return 'area' => NULL,
     * @return 'login_account' => 'test01',
     * @return 'email' => NULL,
     * @return 'mobile' => NULL,
     * @return 'login_type' => 'common',
     * @return 'name' => 'xeno9999',
     * @return 'username' => 'cl',
     * @return 'birthday' => 1475078400,
     * @return 'reg_ip' => '192.168.51.69',
     * @return 'regtime' => 1453368867,
     * @return 'sex' => '1',
     * @return 'point' => 43362,
     * @return 'experience' => 47217,
     * @return 'grade_id' => 5,
     * @return 'grade_name' => '白金会员',
     * @return 'email_verify' => 0,
     */
    public function __getUser($userId)
    {
        if(!$userId)
            throw new LogicException('User ID Is Mustn\'t Be Null');

        $user = kernel::single('sysuser_passport')->memInfo($userId);

        if(!$user)
            throw new LogicException('Don\'t Find The Member With UserId:' . $userId);
        return $user;
    }

    private function __genUser($userData)
    {
        $user = [];
        $user['uid']         = $userData['userId'];
        $user['user_name']   = $userData['login_account'];
        $user['email']       = $userData['email'];
        $user['mobile']      = $userData['mobile'];
        $user['nick_name']   = $userData['name'];
        $user['birthday']    = date('y-m-d', $userData['birthday']);
        $user['created']     = date('y-m-d h:i:s', $userData['regtime']);
        $user['sex']         = $this->__sexMap($userData['sex']);

        return $user;
    }


    private function __sexMap($sexIdInBbc)
    {
        //bbc: 0女   1男 2保密
        //mat: 0未知 1男 2女
        $map = [
            2 => 0,
            0 => 2,
            1 => 1,
        ];

        $sexIdInMatrix = $map[$sexIdInBbc];
        return $sexIdInMatrix;
    }

}

