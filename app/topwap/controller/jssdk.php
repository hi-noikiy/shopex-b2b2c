<?php
class topwap_ctl_jssdk  extends topwap_controller {

	public function __construct(){
		$this->appId = app::get('site')->getConf('site.appId');
		$this->appsecret = app::get('site')->getConf('site.appSecret');
	}

	public function index()
	{
		$timestamp = time();
		$appId = $this->appId;
		$appsecret = $this->appsecret;
		$jsapi_ticket = $this->make_ticket($appId,$appsecret);
		$nonceStr = $this->make_nonceStr();
		if (strstr($_GET['url'],'activity-itemdetail')) {
			$url = $_GET['url'].'&g='.$_GET['g'];
		}else{
			$url = $_GET['url'];
		}
		//$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$signature = $this->make_signature($nonceStr,$timestamp,$jsapi_ticket,$url);

		$signPackage = array(
			"appId"     => $this->appId,
			"appsecret" => $this->appsecret,
			"nonceStr"  => $nonceStr,
			"timestamp" => $timestamp,
			"signature" => $signature,
		);
		return response::json($signPackage);

	}

	public function make_nonceStr()
	{
		$codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		for ($i = 0; $i<16; $i++) {
			$codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
		}
		$nonceStr = implode($codes);
		return $nonceStr;
	}

	public function make_signature($nonceStr,$timestamp,$jsapi_ticket,$url)
	{
		$tmpArr = array(
		'noncestr' => $nonceStr,
		'timestamp' => $timestamp,
		'jsapi_ticket' => $jsapi_ticket,
		'url' => $url
		);
		ksort($tmpArr, SORT_STRING);
		$string1 = http_build_query( $tmpArr );
		$string1 = urldecode( $string1 );
		$signature = sha1( $string1 );
		return $signature;
	}

	public function make_ticket($appId,$appsecret)
	{
		// access_token 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(file_get_contents(DATA_DIR."/wxshare/access_token.json"));
		if (!is_dir(DATA_DIR.'/wxshare')) {
			mkdir(DATA_DIR.'/wxshare', 0755, true);
		}
		if ($data->expire_time < time()) {
			$TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appsecret;
			$json = file_get_contents($TOKEN_URL);
			$result = json_decode($json,true);
			$access_token = $result['access_token'];
			if ($access_token) {
				$data->expire_time = time() + 7000;
				$data->access_token = $access_token;
				$fp = fopen(DATA_DIR."/wxshare/access_token.json", "w");
				fwrite($fp, json_encode($data));
				fclose($fp);
			}
		}else{
			$access_token = $data->access_token;
		}

		// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(file_get_contents(DATA_DIR."/wxshare/jsapi_ticket.json"));
		if ($data->expire_time < time()) {
			$ticket_URL="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
			$json = file_get_contents($ticket_URL);
			$result = json_decode($json,true);
			$ticket = $result['ticket'];
			if ($ticket) {
				$data->expire_time = time() + 7000;
				$data->jsapi_ticket = $ticket;
				$fp = fopen(DATA_DIR."/wxshare/jsapi_ticket.json", "w");
				fwrite($fp, json_encode($data));
				fclose($fp);
			}
		}else{
			$ticket = $data->jsapi_ticket;
		}
		return $ticket;
	}
}


