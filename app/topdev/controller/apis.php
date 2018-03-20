<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class topdev_ctl_apis extends topdev_controller {

    public function search()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $search = input::get('q');
        if( !$search )
        {
            redirect::action('topdev_ctl_index@index')->send();
        }
        $this->runtimePath[] = ['title' => app::get('topdev')->_($search.' 相关')];

        $topapisList = kernel::single('topdev_apis')->getTopApiGroupList();
        foreach( $topapisList as $value )
        {
            foreach( $value['list'] as $apiName => $val )
            {
                if( stristr($apiName,$search) || stristr($val['apidesc'], $search) )
                {
                    $searchData[$apiName] = $val;
                }
            }
        }
        $list['topapi']['v1'] = $searchData;

        $searchData = array();
        $topapisv2List = kernel::single('topdev_apis')->getTopApiGroupList('v2');
        foreach( $topapisv2List as $value )
        {
            foreach( $value['list'] as $apiName => $val )
            {
                if( stristr($apiName,$search) || stristr($val['apidesc'], $search) )
                {
                    $searchData[$apiName] = $val;
                }
            }
        }
        $list['topapi']['v2'] = $searchData;

        $apisList = kernel::single('topdev_apis')->getApiGroupList();
        foreach( $apisList as $row )
        {
            foreach( $row['list'] as $apiName => $val )
            {
                if( stristr($apiName,$search) || stristr($val['apidesc'], $search) )
                {
                    $searchApisData[$apiName] = $val;
                }
            }
        }
        $list['apis'] = $searchApisData;

        $pagedata['activeGroupList'] = $list;
        return $this->page('topdev/apis/search.html', $pagedata);
    }

    public function group()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $apiType = input::get('apitype');
        if( $apiType == 'topapi' )
        {
            $list = kernel::single('topdev_apis')->getTopApiGroupList(input::get('v','v1'));
            $this->runtimePath[] = ['title' => app::get('topdev')->_('APP聚合API列表')];
            $this->runtimePath[] = ['title' => input::get('v','v1')];
            $this->activeMenu = 'APP聚合API';
        }
        else
        {
            $list = kernel::single('topdev_apis')->getApiGroupList();
            $this->runtimePath[] = ['title' => app::get('topdev')->_('系统API列表')];
            $this->activeMenu = '系统API';
        }
        $this->runtimePath[] = ['title' => input::get('group').'相关API'];

        $pagedata['activeGroupList'] = $list[input::get('group')];
        $pagedata['apitype'] = input::get('apitype');
        $pagedata['v'] = input::get('v', 'v1');

        return $this->page('topdev/apis/list.html', $pagedata);
    }

    public function info()
    {
        $apiType = input::get('apitype');

        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $objApis = kernel::single('topdev_apis');
        $v = input::get('v','v1');
        $apis = $objApis->getApiList($apiType, $v);
        $method = input::get('method');
        if( $method && $apis[$method] )
        {
            $apiConf = $apis[$method];
            $handle = $apiConf['uses'];
            list($class, $fun) = explode('@', $handle);
            $fun = $fun ? : 'handle';
            $handlar = new $class;
            $pagedata['method'] = $method;
            $pagedata['apidesc'] = $handlar->apiDescription;
            $pagedata['system_params'] = $objApis->getSystemParams($apiConf['auth'], $apiType, $v);
            $pagedata['params'] = $objApis->getParams($handlar, $apiType);
            $pagedata['response'] = $objApis->getResponse($class, $fun);
            if( method_exists($handlar, 'returnJson') )
            {
                $pagedata['returnJson'] = $handlar->returnJson();
            }

            $pagedata['apitype'] = input::get('apitype');

            if( $apiType == 'topapi' )
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('APP聚合API列表')];
                $this->runtimePath[] = ['title' => $v];
                $groupName = explode('.',$method)[0];
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName]);
                $this->activeMenu = 'APP聚合API';
            }
            else
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('系统API列表')];
                $groupName = kernel::single('topdev_apis')->getGroupName($class);
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName]);
                $this->activeMenu = '系统API';
            }
            $this->runtimePath[] = ['url'=>$url, 'title' => $groupName];
            $this->runtimePath[] = ['title' => $method.'  '.$pagedata['apidesc']];

            $pagedata['v'] = input::get('v', 'v1');

            return $this->page('topdev/apis/info.html', $pagedata);
        }
    }

    public function testView()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $objApis = kernel::single('topdev_apis');
        $v = input::get('v','v1');
        $apiType = input::get('apitype');
        $apis = $objApis->getApiList($apiType, $v);

        $method = input::get('method');
        if( $method && $apis[$method] )
        {
            $apiConf = $apis[$method];
            $handle = $apiConf['uses'];
            list($class, $fun) = explode('@', $handle);
            $fun = $fun ? : 'handle';
            $handlar = new $class;
            $pagedata['groupKey'] = ($apiType == 'apis') ? 'api/'. explode('_',$class)[0] : explode('_',$class)[0];
            $pagedata['method'] = $method;
            $pagedata['apidesc'] = $handlar->apiDescription;
            $pagedata['system_params'] = $objApis->getSystemParams($apiConf['auth'], $apiType, $v);
            $pagedata['params'] = $objApis->getParams($handlar, $apiType);

            $pagedata['apitype'] = input::get('apitype');

            if( $apiType == 'topapi' )
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('APP聚合API列表')];
                $this->runtimePath[] = ['title' => $v];
                $groupName = explode('.',$method)[0];
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName]);
                $this->activeMenu = 'APP聚合API';
            }
            else
            {
                $this->runtimePath[] = ['title' => app::get('topdev')->_('系统API列表')];
                $groupName = kernel::single('topdev_apis')->getGroupName($class);
                $url = url::action('topdev_ctl_apis@group', ['apitype'=>$apiType,'group'=>$groupName, 'v'=>$v]);
                $this->activeMenu = '系统API';
            }
            $this->runtimePath[] = ['url'=>$url, 'title' => $groupName];
            $this->runtimePath[] = ['url'=>url::action('topdev_ctl_apis@info', ['apitype'=>$apiType,'method'=>$method, 'v'=>$v]), 'title' => $method.'  '.$pagedata['apidesc']];

            $pagedata['v'] = input::get('v', 'v1');
            return $this->page('topdev/apis/test.html', $pagedata);
        }
    }

    public function testApi()
    {
        //面包屑
        $this->runtimePath = array(
            ['url'=> url::action('topdev_ctl_index@index'),'title' => app::get('topdev')->_('桌面')],
        );

        $apiType = input::get('apitype');
        $method = trim(input::get('method'));

        $objApis = kernel::single('topdev_apis');
        $v = input::get('v','v1');
        $apis = $objApis->getApiList($apiType, $v);

        $apiParams = input::get('params');
        if( $apiType == 'apis' )
        {
            $url = kernel::base_url(1).kernel::url_prefix().'/api';
            $pagedata['apiParams'] = $apiParams;
            $apiParams['method'] = $method;
            $apiParams['timestamp'] = time();
            $apiParams['sign_type'] = 'MD5';
            $apiParams['sign'] = base_rpc_validate::sign($apiParams,base_certi::token());
        }
        else
        {
            $url = kernel::base_url(1).kernel::url_prefix().'/topapi';
            $pagedata['apiParams'] = $apiParams;
            $apiParams['method'] = $method;
        }

        $runtimestart = microtime(true);
        $result = client::post($url, ['body' => $apiParams])->getBody();
        $runtimestop= microtime(true);
        $runtime = round(($runtimestop - $runtimestart) , 4);

        $pagedata['getUrl'] = $getUrl = $url.'?'.http_build_query($apiParams);
        $pagedata['apiRunTime'] = $runtime;
        $pagedata['filesize'] = kernel::single('topdev_apis')->formatSize(strlen($result));
        $pagedata['result'] = $result;
        $html = view::make('topdev/apis/result.html', $pagedata);
        return response::json(['html'=>strval($html)]);
    }

    public function topapiExport()
    {
        $objApis = kernel::single('topdev_apis');
        $topapisList = kernel::single('topdev_apis')->getTopApiGroupList();
        $apiType = 'topapi';

        $menugroup = [
            'region'=>'地区',
            'logistics'=>'物流',
            'theme'=>'模板',
            'category'=>'品牌类目',
            'user'=>'账户',
            'member'=>'会员',
            'trade'=>'交易',
            'item'=>'商品',
            'cart'=>'购物车',
            'promotion'=>'促销',
            'payment'=>'支付',
            'shop'=>'店铺',
            'content'=>'内容管理',
            'common'=>'通用',
            'message'=>'消息',
        ];

        foreach( $topapisList as $groupName=>$list )
        {
            $topapisList[$groupName]['name'] = $this->menugroup[$groupName];
            foreach($list['list'] as $method=>$apiConf)
            {
                $handle = $apiConf['uses'];
                list($class, $fun) = explode('@', $handle);
                $fun = $fun ? : 'handle';
                $handlar = new $class;
                $topapisList[$groupName]['list'][$method]['method'] = $method;
                $topapisList[$groupName]['list'][$method]['apidesc'] = $handlar->apiDescription;
                $topapisList[$groupName]['list'][$method]['system_params'] = $objApis->getSystemParams($apiConf['auth'], $apiType, $v);
                $topapisList[$groupName]['list'][$method]['params'] = $objApis->getParams($handlar, $apiType);
                if( method_exists($handlar, 'returnJson') )
                {
                    $topapisList[$groupName]['list'][$method]['returnJson'] = $handlar->returnJson();
                }
                else
                {
                    $topapisList[$groupName]['list'][$method]['response'] = $objApis->getResponse($class, $fun);
                }
            }
        }
        $pagedata['topapisList'] = $topapisList;

        $md =  view::make('topdev/apis/markdown.html', $pagedata);

        header("Cache-Control: public");
        header("Content-Type: application/force-download");
        header("Accept-Ranges: bytes");
        header("Content-Disposition: attachment; filename=聚合API.md");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        echo $md;
        exit;
    }
}
