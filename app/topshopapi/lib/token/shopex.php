<?php
//shopex体系 API签名验证
class topshopapi_token_shopex {

    public function check($data)
    {
        $nodeId = $data['to_node_id'];

        if( $nodeId )
        {
            $nodeInfo = app::get('topshopapi')->rpcCall('open.shop.node.get', ['node_id'=>$nodeId]);
            //商家未开通shopex体系
            //API没有签名参数
            //签名未通过
            if( !$nodeInfo || !$data['sign'] || $data['sign'] != $this->__sign($data, $nodeInfo['node_token']) )
            {
                throw new LogicException('签名错误');
            }
        }
        else
        {
            throw new LogicException('签名错误');
        }

        return $nodeInfo['shop_id'];
    }

    private function __sign($params, $token)
    {
        return strtoupper(md5(strtoupper(md5($this->__assemble($params))).$token));
    }

    private function __assemble($params)
    {
        if(!is_array($params))  return null;

        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val)
        {
            if( $key == 'sign' ) continue;
            $sign .= $key . (is_array($val) ? $this->__assemble($val) : $val);
        }
        return $sign;
    }
}

