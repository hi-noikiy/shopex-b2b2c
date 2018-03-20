<?php
class system_data_messenger{

    public function __construct(&$app)
    {
        $this->app = $app;
    }

    public function loadTitle($action,$type,$data="")
    {
        $tmpArr=$data;
        $title = app::get('system')->getConf('messenger.title.'.$action.'.'.$type);
        if($data != "")
        {
            preg_match_all('/<\{\$(\S+)\}>/iU', $title, $result);
            foreach($result[1] as $k => $v)
            {
                $v=explode('.',$v);
                $data=$tmpArr;
                foreach($v as $key => $val)
                {
                    $data=$data[$val];
                    if(is_array($data))
                    {
                        continue ;
                    }
                    else
                    {
                        $title = str_replace($result[0][$k],$data,$title);
                    }
                }
            }
        }
        return $title;
    }

    function loadTmpl($action,$msg,$lang=''){
        $objMdlsystmpl = app::get('system')->model('messenger_systmpl');
        $msg = $this->_getType($msg);
        return $objMdlsystmpl->get('messenger:'.$msg.'/'.$action);
    }

    public function saveActions($action,&$msg)
    {
        $actions = config::get('messenger.actions');
        foreach($actions as $act=>$info)
        {
            if(!$action[$act]) $action[$act] = array();
        }

        foreach($action as $act=>$call)
        {
            app::get('system')->setConf('messenger.actions.'.$act,implode(',',array_keys($call)));
        }
        return true;
    }

    public function saveContent($action,$messenger,$savedata)
    {
        $objMdlsystmpl = app::get('system')->model('messenger_systmpl');
        $messengers = config::get('messenger.messenger');
        $info = $messengers[$messenger];
        if($info['hasTitle']) app::get('system')->setConf('messenger.title.'.$action.'.'.$messenger,$savedata['title']);
        $msg = $this->_getType($messenger);
        return $objMdlsystmpl->set('messenger:'.$msg.'/'.$action,$savedata['content']);
    }

    public function getSenders($act)
    {
        $ret = app::get('system')->getConf('messenger.actions.'.$act);
        return explode(',',$ret);
    }

    public function setSmsSign($sign)
    {
        $result = $this->checkSign($sign,$msg);
        if(!$result){
            throw new \LogicException($msg);
            return false;
        }
        $signs='【'.$sign.'】';
        $entid = base_enterprise::ent_id();
        $token = $this->getForeverToken($entid);
        if(!$token)
        {
            $msg = app::get('system')->_('签名token出错');
            throw new \LogicException($msg);
        }

        //判断是添加还是修改
        $setSmsSign=app::get('system')->getConf('setSmsSign');
        //添加签名
        if(empty($setSmsSign['sign']))
        {
            $params = array(
                'shopexid' => $entid,
                'content' => $signs,
                'token' => $token,
            );
            $result = prism::post('/addcontent/newbytoken',$params);
        }
        else
        { //修改签名
            $params = array(
                'shopexid' => $entid,
                'token' => $token,
                'old_content'=>'【'.$setSmsSign['sign'].'】',
                'new_content' => $signs,
            );
            $result = prism::post('/addcontent/updatebytoken',$params);
        }

        $response = json_decode($result,true);
        if($error = $response['error'])
        {
            //兼容目前出现的“签名不存在”问题
            if($error['code'] == '2010')
            {
                app::get('system')->setConf('setSmsSign', null);
            }
            $msg = $error['message']?$error['message']:"请求设置短信签名出错";
            throw new \LogicException($msg);
        }

        $array=array(
            'sign'=>trim($sign),
        );
        app::get('system')->setConf('review',$response['data']['review']);
        app::get('system')->setConf('setSmsSign', $array);
        return true;
    }

    public function checkSign($sign,&$msg)
    {
        if(mb_strlen(urldecode(trim($sign)),'utf-8') > 8 || mb_strlen(urldecode(trim($sign)),'utf-8') < 2)
        {
            $msg = app::get('system')->_("签名长度为2到8字");
            return false;
        }

        $arr=array('天猫','tmall','淘宝','taobao','1号店','易迅','京东','亚马逊','test','测试');
        for ($i=0; $i <count($arr) ; $i++)
        {
            if(strstr(strtolower($sign),$arr[$i] ))
            {
                $msg = app::get('system')->_("非法签名");
                return false;
            }
        }
        return true;
    }

    public function setSmsConf($postData)
    {
        $this->__checkSmsConf($postData);
        app::get('system')->setConf('messenger.count.'.$postData['tpl_name'].'.'.$postData['tpl_type'], $postData['count']);
        app::get('system')->setConf('messenger.ttl.'.$postData['tpl_name'].'.'.$postData['tpl_type'], $postData['active']);

        return true;
    }

    private function __checkSmsConf($postData)
    {
        if(!$postData)
        {
            throw new \LogicException(app::get('system')->_('表单验证失败'));
        }

        if($postData['tpl_type'] != 'sms')
        {
            throw new \LogicException(app::get('system')->_('模板类型错误'));
        }

        if(!$postData['tpl_name'])
        {
            throw new \LogicException(app::get('system')->_('模板错误'));
        }

        $actions = config::get('messenger.actions');
        if(!array_key_exists($postData['tpl_name'], $actions))
        {
            throw new \LogicException(app::get('system')->_('模板错误'));
        }

        // 验证字段
        $validator = validator::make(
                [
                        'count' => $postData['count'],
                        'active' => $postData['active']
                ],
                [
                        'count' => 'required|Integer',
                        'active' => 'required|Integer',
                ],
                [
                        'count' => app::get('system')->_('请填写单手机号每天可获取验证码次数').'|'.app::get('system')->_('必须是整数'),
                        'active' => app::get('system')->_('验证码有效时间').'|'.app::get('system')->_('必须是整数'),
                ]
        );
        $validator->newFails();

        if((int)$postData['count'] <= 0 || (int)$postData['count'] >100)
        {
            throw new \LogicException(app::get('system')->_('单手机号每天可获取验证码次数不得大于100次'));
        }

        if((int)$postData['active'] <= 0 || (int)$postData['active'] >60)
        {
            throw new \LogicException(app::get('system')->_('验证码有效时间不得大于60分钟'));
        }

        return true;
    }

    private function _getType($msg)
    {
        $messenger = config::get('messenger.messenger');
        return $messenger[$msg]['class'];
    }

    public function setForeverToken()
    {
        $conf = base_setup_config::deploy_info();
        $params['product_code'] = $conf['product_key'];
        $result = prism::post('/auth/auth.gettoken',$params);
        $result = json_decode($result,true);
        if($result['status'] == "success")
        {
            $token = $result['data']['token'];
            $shopexid = $result['data']['shopexid'];
            $redis = redis::scene('system');
            $redis->set('forever_token_'.$shopexid,$token);
            return $token;
        }
        elseif($result['result'] == "error")
        {
            $msg = $result['error']['message'];
            throw new \LogicException($msg);
        }
    }

    public function getForeverToken($shopexid)
    {
        $token = "";

        if(!$shopexid)
        {
            $redis = redis::scene('system');
            $token = $redis->get('forever_token_'.$shopexid);
        }

        if(!$token)
        {
            $token = $this->setForeverToken();
        }

        return $token;
    }

}


