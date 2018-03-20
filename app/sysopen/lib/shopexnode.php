<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class sysopen_shopexnode {

    static $shopCertiId = null;

    static $shopCertiToken = null;

    static $shopNodeId  = null;

    static function register($shopId)
    {
        return self::sendToCenter('node.reg', $shopId);
    }

    static function update($shopId)
    {
        return self::sendToCenter('node.update', $shopId);
    }

    static function sendToCenter($method='node.reg', $shopId)
    {
        $appInfo = app::get('sysopen')->define();
        $conf = base_setup_config::deploy_info();

        // 生成参数...
        $apiData = array(
            'certi_app'      => $method,
            'certificate_id' => self::getCertiId($shopId),
            'node_type'      => $conf['product_key'],
            'url'            => self::getUrl($shopId),
            'version'        => $appInfo['version'],
            'channel_ver'    => '1.0',
            'api_ver'        => '1.2',
            'format'         => 'json',
            'api_url'        => self::getShopApiUrl(),
        );

        //更新时，多带个参数
        if($method == 'node.update')
        {
            $apiData['node_id'] = sysopen_shopexnode::getNodeId($shopId);
        }

        ksort($apiData);

        foreach($apiData as $key => $value)
        {
            $str.=$value;
        }
        $apiData['certi_ac'] = strtoupper(md5($str. self::getCertiToken($shopId)));

        logger::info('register to center request data : ' . json_encode($apiData));
        $result = client::post(config::get('link.license_center'), ['body' => $apiData, 'timeout'=>6])->json();

        logger::info('register to center result data : ' . json_encode($result));
        if ($result['res'] == 'succ')
        {
            //license_id token node_id
            return $shopId ? $result['info'] : self::setNodeInfo($result['info']);
        }
        else
        {
            return false;
        }
    }

    //获取到证书ID
    static function getNodeId($shopId)
    {
        return $shopId ? self::$shopNodeId : self::getNodeInfo()['node_id'];
    }

    //获取到证书ID
    static function getCertiId($shopId)
    {
        return $shopId ? self::$shopCertiId : base_certi::certi_id();
    }

    static function getCertiToken($shopId)
    {
        return $shopId ? self::$shopCertiToken : base_certi::token();
    }

    static function setShopCertiId($certificateId, $token, $nodeId)
    {
        self::$shopCertiId  = $certificateId;
        self::$shopCertiToken = $token;

        if( $nodeId )
        {
            self::$shopNodeId = $nodeId;
        }
    }

    static function getUrl($shopId)
    {
        if( $shopId )
        {
            return kernel::base_url(1).kernel::url_prefix().'/shophandshake?'.$shopId;
        }
        else
        {
            return kernel::base_url(1);
        }
    }

    static function getShopApiUrl()
    {
        return kernel::base_url(1).kernel::url_prefix().'/shop/api';
    }

    static function getPlatformApiUrl()
    {
        return kernel::base_url(1).kernel::url_prefix().'/matrix/api';
    }

    static function setNodeInfo($node)
    {
        return app::get('sysopen')->setConf('shopexmatrix_node', serialize($node));
    }

    static function deleteNodeInfo()
    {
        return app::get('sysopen')->setConf('shopexmatrix_node', '');
    }

    static function getNodeInfo()
    {
        $node = app::get('sysopen')->getConf('shopexmatrix_node');
        return $node ?  unserialize($node) : null;
    }
}
