<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_ctl_certificate extends desktop_controller{

    function index(){

        $this->Certi = base_certi::get('certificate_id');
        $this->Token = base_certi::get('token');
        if(empty($this->Certi) ||empty($this->Token)){
            $pagedata['license']=false;
        }else{
            $pagedata['license']=true;
        }
        $pagedata['certi_id']=$this->Certi;
        $pagedata['debug']=false;

        return $this->page('desktop/certificate.html', $pagedata);
    }

    function upLicense(){
    	if ( $_FILES ){
    		if ( $_FILES['license']['name'] ){
	    		$fileName = explode( '.', $_FILES['license']['name'] );
	    		if ( 'CER' != $fileName['1'] ){
					$this->begin();
					$this->end(false, app::get('desktop')->_("证书格式不对"));
	    		}
	    		else {
			        $content = file_get_contents($_FILES['license']['tmp_name']);
			        list($certificate_id,$token) = explode('|||',$content);
					/** 验证证书是否合法 **/
					$sys_params = base_setup_config::deploy_info();
					$code = md5(microtime());
                    redis::scene('system')->set('net.login_handshake', $code);
                    
					$app_exclusion = app::get('base')->getConf('system.main_app');
					/** 得到框架的总版本号 **/
					$obj_apps = app::get('base')->model('apps');
					$tmp = $obj_apps->getList('*',array('app_id'=>'base'));
					$app_xml = $tmp[0];
					$app_xml['version'] = $app_xml['local_ver'];
					$conf = base_setup_config::deploy_info();
					$data = array(
						'certi_app'=>'open.login',
						'certificate_id'=>$certificate_id,
						'url' => kernel::base_url(1),
						'version'=>'0.14',
						'ver_detail'=> $app_xml['version'],            
						'result' => $code,
						'format'=>'json',
					);
					ksort($data);
					foreach($data as $key => $value){
						$str.=$value;
					}
					$data['certi_ac'] = md5($str.$token);
                    $posturl = config::get('link.license_center');
                    try {
                        $result = client::post($posturl, ['body' => $data, 'timeout'=>6])->json();
                    }
                    catch (Exception $e) {
                        $result = [];
                    }
					
					if ($result['res'] != 'succ'){
						$this->begin();
						$this->end(false, app::get('desktop')->_("上传证书无效"));
					}
			        $result = base_certi::set_certificate(array('certificate_id'=>$certificate_id,'token'=>$token));
			        if(!$result){
						$this->begin();
						$this->end(false, app::get('desktop')->_("证书重置失败,请先上传文件"));
			        }else{						
						$this->begin();
						$this->end(true, app::get('desktop')->_("证书上传成功"));
			        }
	    		}
    		}
    		else {
				$this->begin();
				$this->end(false, app::get('desktop')->_("请选择要上传的文件"));
    		}
    	}else{
			
		}
    }
    function download(){
        header("Content-type:application/octet-stream;charset=utf-8");
        header("Content-Type: application/force-download");
        header("Cache-control: private");
        $this->fileName = 'CERTIFICATE.CER';
        header("Content-Disposition:filename=".$this->fileName);

        $this->Certi = base_certi::get('certificate_id');
        $this->Token = base_certi::get('token');
        echo $this->Certi;
        echo '|||';
        echo $this->Token;
    }
    function delete(){
        $this->begin();
        base_certi::del_certi();
        //base_certi::register();
        $this->end();
    }

}

