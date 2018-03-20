<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class base_rpc_client
{
    public function call($method, $parameters = array(), $appId = 'default', $identity="")
    {
        if($identity)
        {
            switch($identity)
            {
            case "buyer":
                pamAccount::setAuthType('sysuser');
                $oauth['auth_type'] = pamAccount::getAuthType('sysuser');
                break;
            case "seller":
                pamAccount::setAuthType('sysshop');
                $oauth['auth_type'] = pamAccount::getAuthType('sysshop');
                break;
            case "shopadmin":
                pamAccount::setAuthType('desktop');
                $oauth['auth_type'] = pamAccount::getAuthType('desktop');
                break;
            }
            $oauth['account_id'] = pamAccount::getAccountId();
            $oauth['account_name'] = pamAccount::getLoginName();
        }

        $parameters['oauth'] = $oauth;

        if( $this->distribute() )
        {
            return $this->callOutside($method, $parameters, $appId);
        }
        else
        {
            return $this->callInternal($method, $parameters);
        }
    }

    private function distribute()
    {
        return config::get('prism.prismMode') ? true : false;
    }

    protected function callInternal($apiMethod, $parameters = array())
    {
        $apis = config::get('apis.routes');

        if (array_key_exists($apiMethod, $apis))
        {
            list($class, $method) = explode('@', $apis[$apiMethod]['uses']);
        }
        else
        {
            throw new InvalidArgumentException("Api [$apiMethod] not defined");
        }

        $instance = new $class();
        if( !method_exists($instance, $method) )
        {
            throw new InvalidArgumentException("Api [$apiMethod] method [$method] not defined");
        }

        $apiParams = $instance->getParams();

        //验证数据
        //通过传入数据和api原定义的类型进行比对
        $realApiParams = ecos_validatorApiParams($apiParams['params'], $parameters);

        //验证json结构的参数
        //$realApiParams 为API传入参数验证后API定义的参数
        //以前的API有些在API中未定义确使用了，因此该API参数为兼容暂时不使用
        $realApiParams = ecos_validatorApiJsonParams($apiParams['params'], $parameters, $realApiParams);

        //是否需要强制使用API定义的参数
        if( $instance->use_strict_filter )
        {
            $apiParameters = apiUtil::pretreatment($realApiParams, $apiParams);
        }
        else
        {
            $apiParameters = apiUtil::pretreatment($parameters, $apiParams);
        }

        return call_user_func(array($instance, $method), $apiParameters);
    }

    protected function callOutside($method, $params, $appId)
    {
        $caller = new base_prism_caller;
        return $caller->call($method, $params, $appId);
    }
}

