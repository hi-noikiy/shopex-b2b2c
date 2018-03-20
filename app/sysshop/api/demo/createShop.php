<?php

class sysshop_api_demo_createShop {

    public $apiDescription = "创建商家角色";

    public function getParams()
    {
        $return['params'] = array(
            'shop_name'             => ['type'=>'string','valid'=>'required','description'=>'店铺名称','default'=>'','example'=>'onex店'],
            'shop_descript'         => ['type'=>'string','valid'=>'','description'=>'店铺详情','default'=>'','example'=>'专卖onex产品'],
            'shop_logo'             => ['type'=>'string','valid'=>'','description'=>'店铺logo','default'=>'','example'=>'http://images.bbc.shopex123.com/images/24/a3/6e/8a701392f5447a26c7a489e39e072b512890c54a.jpg'],
            'shopuser_name'         => ['type'=>'string','valid'=>'required','description'=>'店主姓名','default'=>'','example'=>'张三'],
            'shopuser_identity'     => ['type'=>'string','valid'=>'','description'=>'店主身份证号','default'=>'','example'=>'张三'],
            'shopuser_identity_img' => ['type'=>'string','valid'=>'','description'=>'店主身份证照片','default'=>'','example'=>'http://images.bbc.shopex123.com/images/24/a3/6e/8a701392f5447a26c7a489e39e072b512890c54a.jpg'],
            'shop_area'             => ['type'=>'string','valid'=>'','description'=>'店铺地区','default'=>'','example'=>'上海市徐汇区'],
            'shop_addr'             => ['type'=>'string','valid'=>'required','description'=>'店铺地址','default'=>'','example'=>'桂林路396号2号楼'],
            'mobile'                => ['type'=>'string','valid'=>'required','description'=>'店主手机号','default'=>'','example'=>'13312341234'],
            'email'                 => ['type'=>'string','valid'=>'required','description'=>'店主邮箱','default'=>'','example'=>'1234@qq.com'],
            'login_account'         => ['type'=>'string','valid'=>'','description'=>'店主账号','default'=>'','example'=>'demo'],
            'login_password'        => ['type'=>'string','valid'=>'required','description'=>'店主账号登录密码','default'=>'','example'=>'demo123'],
        );

        return $return;
    }

    /**
     *
     * -------shop infomation start ------
     * shop_name string 店铺名称
     * shop_descript string 店铺详情
     * shop_logo image 店铺的logo
     * shopuser_name string 店主名称
     * shopuser_identity string 店主身份证号码
     * shopuser_identity_img string 店主身份证照片
     * shop_area string 店铺地区
     * shop_addr string 店铺地址
     * mobile 店主电话号码
     * email 店主邮箱
     * -------shop infomation end ------
     *
     * -------seller infomation start ------------
     * login_account string 登录用户名
     * login_password string 登录密码
     * psw_confirm string 密码重复一次
     * shop_id int 店铺id
     * name string 用户名称
     * mobile string 用户手机号
     * email string 用户邮箱
     * -------seller infomation end ------------
     *
     *
     */
    public function create($params)
    {

        try{
            db::connection()->beginTransaction();
            $default_img = "http://images.bbc.shopex123.com/images/24/a3/6e/8a701392f5447a26c7a489e39e072b512890c54a.jpg";

            $shopInfo = [
                'shop_name' => $params['shop_name'].'（自营店铺）',
                'shop_type' => 'self',
                'status' => 'active',
                'seller_id' => 0,
                'open_time' => time(),
                'shop_descript' => $params['shop_descript'] ? : "演示店铺",
                'shop_logo' => $params['shop_logo'] ? : $default_img,
                'shopuser_name' => $params['shopuser_name'],
                'shopuser_identity' => $params['shopuser_identity'] ? :"11111111111111",
                'shopuser_identity_img' => $params['shopuser_identity_img'] ? : $default_img,
                'shop_area' => $params['shop_area'] ? : "上海市",
                'shop_addr' => $params['shop_addr'],
                'mobile' => $params['mobile'],
                'email' => $params['email'],
            ];

            $this->__checkShopInfo($shopInfo);
            $objShop = kernel::single('sysshop_data_shop');

            $shop_id = $objShop->saveShop($shopInfo, true);
            $si = $objShop->getShopById($shop_id);



            $sellerInfo = [
                'login_account' => $params['login_account'] ? : 'shop'.$params['mobile'],
                'login_password' => $params['login_password'],
                'psw_confirm' => $params['login_password'],
                'shop_id' => $shop_id,
                'name' => $params['shopuser_name'],
                'mobile' => $params['mobile'],
                'email' => $params['email'],
            ];
            $this->__checkSellerInfo($sellerInfo);
            $objSeller = kernel::single('sysshop_data_seller');
            $objSeller->saveSelf($sellerInfo, true);
            $this->__authMobile($params['mobile']);

        }catch(Exception $e){
            db::connection()->rollback();
            throw $e;
        }
        db::connection()->rollback();
      //db::connection()->commit();
        return ['shop_id'=>$shop_id];
    }

    private function __checkShopInfo($postdata)
    {
        $validator = validator::make(
            [explode("（",$postdata['shop_name'])[0],
             $postdata['shop_descript'],
             $postdata['shopuser_name'],
             $postdata['shop_area'],
             $postdata['shop_addr'],
             $postdata['mobile'],
            ],
            ['required|max:20',
            'required|max:200',
            'required|max:20',
            'required|max:20',
            'required|max:50',
             'required|mobile',
            ],
            ['店铺名称不能为空!|店铺名称最大不能超过20个字符!',
             '店铺描述不能为空!|店铺描述最大不能超过200个字符!',
             '店主姓名不能为空!|店主姓名最大不能超过20个字符!',
             '所在地区不能为空!|所在地区最大不能超过20个字符!',
             '详细地址不能为空!|详细地址最大不能超过50个字符!',
             '手机号码不能为空!|手机格式不正确！',
            ]
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();

            foreach( $messages as $error )
            {
                throw new LogicException($error[0]);
            }
        }
        return true;
    }

    private function __checkSellerInfo($postdata)
    {
        $validator = validator::make(
            [$postdata['login_account']],['chinese'],['用户名不能用纯数字或中文!']
        );
        if ($validator->fails())
        {
            $messages = $validator->messagesInfo();
            foreach( $messages as $error )
            {
                throw new LogicException($error[0]);
            }
        }


        return true;
    }

    private function __randChar($length)
    {
          $str = null;
          $strPol = "abcdefghijklmnopqrstuvwxyz";
          $max = strlen($strPol)-1;

          for($i=0;$i<$length;$i++){
              $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
          }

          return $str;
    }


    private function __authMobile($mobile)
    {
        app::get('sysshop')->model('seller')->update(['auth_type'=>'AUTH_MOBILE'], ['mobile'=>$mobile]);

        return true;
    }

}

