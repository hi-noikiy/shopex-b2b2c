<?php
/**
 *
 *防止重复提交
 *
 */

class topshop_middleware_formDuplication{
	public function __construct(){

	}

	public function handle($request, Closure $next)
	{
		$token = input::get('_token');
		if($token &&  $token == $_SESSION[$token]){
			unset($_SESSION[$token]);
			return $next($request);
		}
		else
		{
			return response::json(array(
				'error'=>true,
				'message'=>'请勿重复提交数据',
			));
		}
	}
}