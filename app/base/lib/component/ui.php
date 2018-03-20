<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.com/license/gpl GPL License
 */

class base_component_ui {

    var $base_dir='';
    var $base_url='';
    static $inputer = array();
    static $_ui_id = 0;
    var $_form_path = array();
    private $_pageid = null;

    function __construct(){
    }

    function table_begin(){
        return '<table>';
    }

    function table_head($headers){
        return '<thead><th>'.implode('</th><th>',$headers).'</th></thead>';
    }

    function resource($params=null)
    {
        if ($app = $params['app'])
        {
            $app = app::get($app);
            $path = isset($params['path']) && is_string($params['path']) ? $params['path'] : '';
            $url = $app->res_url.'/'.trim($path, '/');
            return $url;
        }
        return kernel::base_url(1);
    }

    function table_colset(){
    }

    function table_panel($html){
        return '<div>'.implode('', $html).'</div>';
    }

    function table_rows($rows){
        foreach($rows as $row){
            $return[] = '<tr>';
            foreach($row as $k=>$v){
                $return[]=$v;
            }
            $return[] = '</tr>';
        }
        return implode('',$return);
    }

    function table_end(){
        return '</table>';
    }

    function img($params){
        if(is_string($params)){
            $params = array('src'=>$params);
        }
        if(empty($params['app'])) throw new \InvalidArgumentException('img tag missing app argument. detail:'.$params['src']);

        $app = app::get($params['app']);
        $app_id = $app->app_id;

        $params['src'] = $app->res_url.'/images/'.$params['src'];
        unset($params['lib']);
        return utils::buildTag($params,'img');
    }

    function input($params){
        if($params['params']){
            $p = $params['params'];
            unset($params['params']);
            $params = array_merge($p,$params);
        }

        if(is_array($params['type'])){
            $params['options'] = $params['type'];
            $params['type'] = 'select';
        }
        if(!array_key_exists('value',$params) && array_key_exists('default',$params)){
            $params['value'] = $params['default'];
        }
        if(!$params['id']){
            $params['id'] = $this->new_dom_id();
        }

        if($this->input_element($params['type'])){
            return $this->input_element($params['type'],$params);
        }else{
            return $this->input_element('default',$params);
        }
    }

    function input_element($type,$params=false){

        if(!self::$inputer){
            if(kernel::is_online()){
                self::$inputer = kernel::servicelist('html_input');
            }else{
                self::$inputer = array('base_view_input' => new base_view_input);
            }
        }

        if($params===false){
            foreach(self::$inputer as $inputer){
                if(method_exists($inputer,'input_'.$type)){
                    return true;
                }
            }
        }else{
            foreach(self::$inputer as $inputer){
                if(method_exists($inputer,'input_'.$type)){
                    $html = $inputer->{'input_'.$type}($params);
                }
            }
            return $html;
        }
        return false;
    }

    function form_start($params=null){

        if(is_string($params)){
            $params = array('action'=>$params);
        }
        if(!$params['action']){
            $params['action'] = '?'.$_SERVER['QUERY_STRING'];
        }

        array_unshift($this->_form_path,$params);

        $return = '';
        if($params['title']){
            $return.='<h4>'.$params['title'].'</h4>';
            unset($params['title']);
        }

        $return .='<div class="tableform'.($params['tabs']?' tableform-tabs':'').'">';

        if($params['tabs']){

            $this->form_tab_html = array();
            $dom_tab_ids = array();
            $current = false;

            foreach($params['tabs'] as $k=>$tab){
                $dom_id = $this->new_dom_id();
                $dom_tab_ids[$k] = $dom_id;
                if($current){
                    $style = 'style="display:none"';
                }else{
                    $style = '';
                    $current = true;
                }
                $this->form_tab_html[$k] = '<div class="division" id="'.$dom_id.'" '.$style.'><table width="100%" cellspacing="0" cellpadding="0">';
            }

            $return.='<div class="tabs-wrap clearfix"><ul>';
            $current = false;
            foreach($params['tabs'] as $k=>$tab){
                if($current){
                    $style = '';
                }else{
                    $style = ' current';
                    $current = true;
                }
                $return.='<li id="_'.$dom_tab_ids[$k].'" class="tab'.
                    $style.'" onclick="setTab([\''.
                    $dom_tab_ids[$k].'\',[\''.implode('\',\'',$dom_tab_ids).'\']],[\'current\'])"><span>'.$tab.'</span></li>';
            }
            $return.='</ul>';

            $this->_form_path[0]['element_started'] = true;
        }

        return utils::buildTag($params,'form',false).$return;
    }

    function form_input($params){
        if(!isset($params['id'])){
            $params['id'] = $this->new_dom_id();
        }
        if(isset($params['tab'])){
            $tab = $params['tab'];
            unset($params['tab']);
        }

        $return ='';

        if(!$this->_form_path[0]['element_started']){
            $return.=<<<EOF
    <div class="division">
        <table width="100%" cellspacing="0" cellpadding="0">
EOF;
            $this->_form_path[0]['element_started'] = true;
        }
        if($params['helpinfo']) $span = '<label class="help">'.$params['helpinfo'].'</label>';
        else $span='';
        if (isset($params['style']) && $params['style'] && $params['style'] == 'display:none;')
        {
            $return.='<tr style="display:none;"><th>'.($params['required']?'<em class="red">*</em>':'').'<label for="'.$params['id'].'">'.$params['title'].'</label>'.
                     '</th><td>'.$this->input($params).$span.'</td></tr>';
        }
        else
            $return.='<tr><th>'.($params['required']?'<em class="red">*</em>':'').'<label for="'.$params['id'].'">'.$params['title'].'</label>'.
                     '</th><td>'.$this->input($params).$span.'</td></tr>';
        if(isset($this->form_tab_html[$tab])){
            $this->form_tab_html[$tab].=$return;
            return '';
        }else{
            return $return;
        }
    }

    static function new_dom_id(){
        return 'dom_el_'.substr(md5(time()),0,6).intval(self::$_ui_id++);
    }

    function form_end($options = []){
        $has_ok_btn=isset($options['has_ok_btn']) ? (bool)$options['has_ok_btn'] : true;
        $btn_txt=isset($options['btn_txt']) ? $options['btn_txt'] : '确定';
        if($this->_form_path[0]['element_started']){
            $return .='</table></div>';
        }

        foreach((array)$this->form_tab_html as $html){
            $return.=$html.'</table></div>';
        }

        if($has_ok_btn){
            $return .='<div class="table-action">'.$this->button(array(
                'type'=>'submit',
                'class'=>'btn-primary',
                'label'=>$btn_txt,
            )).'</div>';
        };

        array_shift($this->_form_path);
        if($this->form_tab_html){
            $return.='</div>';
        }
        $return .='</div></form>';
        $this->form_tab_html = null;
        return $return;
    }

    function button($params){
        if($params['class']){
            $params['class'] = 'btn '.$params['class'];
        }else{
            $params['class'] = 'btn';
        }

        if($params['icon']){
            if(empty($params['app'])) throw new \InvalidArgumentException('button tag if exisiting icon need app argument'. var_export($params, 1));
            $icon = '<i class="btn-icon"><i class="'.$params['icon'].'"></i>'.'</i>';
            $params['class'] .= ' btn-has-icon';
            unset($params['icon']);
        }
        if($params['icon_l']) {
            $icon_l = '<q class="icon">'.$params['icon_l'].'</q>';
            // $params['class'] .= ' btn-has-icon';
            unset($params['icon_l']);
        }
        if($params['icon_r']) {
            $icon_r = '<q class="f-icon">'.$params['icon_r'].'</q>';
            // $params['class'] .= ' btn-has-icon';
            unset($params['icon_r']);
        }

        if($params['label']){
            $label = htmlspecialchars($params['label']);
            unset($params['label']);
        }

        $type = $params['type'];
        if($type=='link'){
            $element = 'a';
            unset($params['link']);
        }else{
            $element = 'button';
            if($params['href'] && !strpos($params['href'], 'javascript:')){
                $params['onclick'] = 'W.page(\''.$params['href'].'\')';
                unset($params['href']);
            }
            if($type!='submit'){
                $params['type'] = 'button';
            }
        }

        if($params['dropmenu']){
            if(!$params['id']){
                $params['id'] = $this->new_dom_id();
            }

            if($type!='dropmenu'){
                $element = 'span';
                $class .= ' btn-drop-menu drop-active';
                $drop_handel_id = $params['id'].'-handel';
                $dropmenu = '<img dropfor="'.$params['id'].'"
                    id="'.$drop_handel_id.'" dropmenu='.$params['dropmenu']
                    .' src="'.app::get('base')->res_url.'/images/transparent.gif" class="drop-handle drop-handle-stand" />';
                unset($params['dropmenu']);
            }else{
                $drop_handel_id = $params['id'];
                $dropmenu = '<img src="'.app::get('base')->res_url.'/images/transparent.gif" class="drop-handle" />';
            }
            $scripts = '<script>new DropMenu("'.$drop_handel_id.'",{'.$params['dropmenu_opts'].'});';
            $scripts .= '</script>';
        }

        return utils::buildTag($params,$element,0).''.$icon.$icon_l.$label.$dropmenu.$icon_r.'</'.$element.'>'.$scripts;
    }

    function getVer($flag=true, $ver=null)
    {
        return '?'.view::getCacheVersion();
    }

    function script($params){
        if(empty($params['app'])) throw new \InvalidArgumentException('script tag missing app argument. detail:'.$params['src']);
        $app = app::get($params['app']);
        $file = $params['src'];

        $debug = config::get('app.debug', false);
        $dir = 'scripts';
        if(!$debug) {
            $file = preg_replace('/(?:.min)?\.js$/i', '.min.js', $params['src']);
        }

        if (is_dir($app->res_dir.'/'.$dir))
            $file = $app->res_url.'/'.$dir.'/'.$file;
        else
            $file = $app->res_url.'/'.$file;
        if($params['content']){
            return '<script>'.file_get_contents($file).'</script>';
        }else{
            $version = $this->getVer($debug);
            return '<script src="'.$file.$version.'"></script>';
        }
    }

    function css($params)
    {
        if(empty($params['app'])) throw new \InvalidArgumentException('css tag missing app argument. detail:'.$params['src']);
        $app = app::get($params['app']);
        $file = $params['src'];

        $default = array(
            'rel' => 'stylesheet',
            'media' => 'screen, projection',
        );
        $debug = config::get('app.debug', false);
        $dir = 'stylesheets';
        if(!$debug) {
            $file = preg_replace('/(?:.min)?\.css$/i', '.min.css', $params['src']);
        }

        if (is_dir($app->res_dir.'/'.$dir))
            $file = $app->res_url.'/'.$dir.'/'.$file;
        else
            $file = $app->res_url.'/'.$file;

        if(isset($params['pdir'])) unset($params['pdir']);
        if(isset($params['src'])) unset($params['src']);
        if(isset($params['app'])) unset($params['app']);
        $params = count($params) ? $params+$default : $default;
        foreach($params AS $k=>$v){
            $ext .= sprintf('%s="%s" ', $k, $v);
        }
        $version = $this->getVer($debug);
        return sprintf('<link href="%s" %s/>', $file.$version, $ext);
    }//End Function

    function pager($params){

        if(substr($params['link'],0,11)=='javascript:'){
            $tag = 'span';
            $this->pager_attr = 'onclick';
            $params['link'] = substr($params['link'],11);
        }else{
            $tag = 'a';
            $this->pager_attr = 'href';
        }

        $this->pager_tag = $tag;

        if(!$params['current'])$params['current'] = 1;
        if(!$params['total'])$params['total'] = 1;
        if($params['total']<2){
            return '';
        }

        if(!$params['nobutton']){
            if($params['current']>1){
                $prev = '<'.$tag.' '.$this->pager_attr.'="'.sprintf($params['link'],$params['current']-1)
                    .'" class="prev">&laquo;</'.$tag.'>';
            }else{
                $prev = '<span class="prev disabled">&laquo;</span>';
            }

            if($params['current']<$params['total']){
                $next = '<'.$tag.' '.$this->pager_attr.'="'.sprintf($params['link'],$params['current']+1)
                    .'" class="next">&raquo;</'.$tag.'>';
            }else{
                $next = '<span class="next disabled">&raquo;</span>';
            }
        }

        $c = $params['current']; $t=$params['total']; $v = array();  $l=$params['link'];;

        if($t<11){
            $v[] = $this->pager_link(1,$t,$l,$c);
            //123456789
        }else{
            if($t-$c<8){
                $v[] = $this->pager_link(1,3,$l);
                $v[] = $this->pager_link($t-8,$t,$l,$c);
                //12..50 51 52 53 54 55 56 57
            }elseif($c<10){
                $v[] = $this->pager_link(1,max($c+3,10),$l,$c);
                $v[] = $this->pager_link($t-1,$t,$l);
                //1234567..55
            }else{
                $v[] = $this->pager_link(1,3,$l);
                $v[] = $this->pager_link($c-2,$c+3,$l,$c);
                $v[] = $this->pager_link($t-1,$t,$l);
                //123 456 789
            }
        }
        $links = implode('&hellip;',$v);

        return <<<EOF
    <div class="pager">
     <div class="pagernum">
      {$prev}{$links}{$next}
     </div>
    </div>
EOF;
    }

    private function pager_link($from,$to,$l,$c=null){
        for($i=$from;$i<$to+1;$i++){
            if($c==$i){
                $r[]=' <span class="current num">'.$i.'</span> ';
            }else{
                $r[]=' <'.$this->pager_tag.' '.$this->pager_attr.'="'.sprintf($l,$i).'" class="num"'.'>'.$i.'</'.$this->pager_tag.'> ';
            }
        }
        return implode(' ',$r);
    }

    function lang_script($params){
        if(empty($params['app'])) throw new \InvalidArgumentException('lang script tag missing app argument. detail:'.$params['src']);
        $app = app::get($params['app']);
        $lang = kernel::get_lang();

        $debug = config::get('app.debug', false);
        if($params['pdir'] && !$debug){
            $pdir = $params['pdir'];
        }else{
            $pdir = 'js';
        }

        $src = $pdir. '/' . $params['src'];

        $file = $app->lang_url . '/' . $lang . '/' . $src;
        

        $version = $this->getVer($debug);
        return '<script src="'.$file.$version.'"></script>';
    }


    public function pageid() {
        if(is_null($this->_pageid)){
            $obj = request::instance();
            $key = md5(sprintf('%s_%s_%s_%s', $obj->get_app_name(), $obj->get_ctl_name(), $obj->get_act_name(), serialize($obj->get_params())));
            $this->_pageid = base_convert(strtolower($key), 16, 10);
            $this->_pageid = substr($this->dec2any($this->_pageid), 4, 8);
        }
        return $this->_pageid;
    }//End Function

    private function dec2any($num, $base=62, $index=false) {
        if (! $base ) {
            $base = strlen( $index );
        } else if (! $index ) {
            $index = substr( "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ" ,0 ,$base );
        }
        $out = "";
        for ( $t = floor( log10( $num ) / log10( $base ) ); $t >= 0; $t-- ) {
            $a = floor( $num / pow( $base, $t ) );
            $out = $out . substr( $index, $a, 1 );
            $num = $num - ( $a * pow( $base, $t ) );
        }
        return $out;
    }

    function desktoppager($params){

        if(substr($params['link'],0,11)=='javascript:'){
            $tag = 'span';
            $this->pager_attr = 'onclick';
            $params['link'] = substr($params['link'],11);
        }else{
            $tag = 'a';
            $this->pager_attr = 'href';
        }

        $this->pager_tag = $tag;

        if(!$params['current'])$params['current'] = 1;
        if(!$params['total'])$params['total'] = 1;
        if($params['total']<2){
            return '';
        }

        if(!$params['nobutton']){
            if($params['current']>1){
                $first = '<'.$tag.' '.$this->pager_attr.'="'.sprintf($params['link'],1)
                    .'" class=""><i class="fa fa-angle-double-left"></i></'.$tag.'>';
                $prev = '<'.$tag.' '.$this->pager_attr.'="'.sprintf($params['link'],$params['current']-1)
                    .'" class="prev"><i class="fa fa-angle-left"></i></'.$tag.'>';
            }else{
                $first = '<span class="disabled"><i class="fa fa-angle-double-left"></i></span>';
                $prev = '<span class="prev disabled"><i class="fa fa-angle-left"></i></span>';
            }

            if($params['current']<$params['total']){
                $next = '<'.$tag.' '.$this->pager_attr.'="'.sprintf($params['link'],$params['current']+1)
                    .'" class="next"><i class="fa fa-angle-right"></i></'.$tag.'>';
                $last = '<'.$tag.' '.$this->pager_attr.'="'.sprintf($params['link'],$params['total'])
                    .'" class=""><i class="fa fa-angle-double-right"></i></'.$tag.'>';
            }else{
                $next = '<span class="next disabled"><i class="fa fa-angle-right"></i></span>';
                $last = '<span class="disabled"><i class="fa fa-angle-double-right"></i></span>';
            }
        }

        $c = $params['current']; $t=$params['total']; $v = array();  $l=$params['link'];;

        if($t<11){
            $v[] = $this->pager_link(1,$t,$l,$c);
            //123456789
        }else{
            if($t-$c<8){
                $v[] = $this->pager_link(1,3,$l);
                $v[] = $this->pager_link($t-8,$t,$l,$c);
                //12..50 51 52 53 54 55 56 57
            }elseif($c<7){
                $v[] = $this->pager_link(1,max($c+3,7),$l,$c);
                $v[] = $this->pager_link($t-1,$t,$l);
                //1234567..55
            }else{
                $v[] = $this->pager_link(1,3,$l);
                $v[] = $this->pager_link($c-2,$c+3,$l,$c);
                $v[] = $this->pager_link($t-1,$t,$l);
                //123 456 789
            }
        }
        $links = implode('&hellip;',$v);

        return <<<EOF
    <div class="pager">
     <div class="pagernum">
      {$first}{$prev}{$links}{$next}{$last}
     </div>
    </div>
EOF;
    }



}
