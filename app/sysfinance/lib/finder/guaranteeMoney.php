<?php
/**
* 保证金
*/
class sysfinance_finder_guaranteeMoney
{
    public $column_edit = '操作';
    public $column_edit_order = 3;
    public $column_edit_width = 200;
    public $detail_basic;
    public $detail_recharge;
    public $detail_expense;
    public $detail_setGuaranteeMoney;

    public function __construct($app){
        $this->app = $app;

        $this->detail_basic = app::get('sysfinance')->_('基本信息');
        $this->detail_recharge = app::get('sysfinance')->_('充值');
        $this->detail_expense = app::get('sysfinance')->_('扣款');
        $this->detail_setGuaranteeMoney = app::get('sysfinance')->_('额度调整');
        $this->objMdlGuranteeMoney = app::get('sysfinance')->model('guaranteeMoney');
    }

    public function column_edit(&$colList, $list)
    {
        foreach($list as $k => $row){
            $colList[$k] = $this->_column_edit($row);
        } 
    }

    public function _column_edit($row){
        $arr = array(
            'app'=>$_GET['app'],
            'ctl'=>$_GET['ctl'],
            'act'=>$_GET['act'],
            'finder_id'=>$_GET['_finder']['finder_id'],
            'action'=>'detail',
            'finder_name'=>$_GET['_finder']['finder_id'],
        );

        $newu = http_build_query($arr,'','&');
        $arr_link = array(
            'info'=>array(
                'detail_basic'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_basic&id='.$row['shop_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysfinance')->_('基本信息'),
                    'target'=>'tab',
                ),
            ),
            'finder'=>array(
                'detail_recharge'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_recharge&id='.$row['shop_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysfinance')->_('充值'),
                    'target'=>'tab',
                ),
                'detail_expense'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_expense&id='.$row['shop_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysfinance')->_('扣款'),
                    'target'=>'tab',
                ),
                'detail_setGuaranteeMoney'=>array(
                    'href'=>'javascript:void(0);',
                    'submit'=>'?'.$newu.'&finderview=detail_setGuaranteeMoney&id='.$row['shop_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('sysfinance')->_('额度调整'),
                    'target'=>'tab',
                ),
            ),
        );

        $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('guaranteeMoney_recharge')){
            unset($arr_link['finder']['detail_recharge']);
        }
        if(!$permObj->has_permission('guaranteeMoney_expense')){
            unset($arr_link['finder']['detail_expense']);
        }
        if(!$permObj->has_permission('guaranteeMoney_adjustment')){
            unset($arr_link['finder']['detail_setGuaranteeMoney']);
        }

        $pagedata['arr_link'] = $arr_link;
        $pagedata['handle_title'] = app::get('sysfinance')->_('编辑');
        $pagedata['is_active'] = 'true';

        return view::make('sysfinance/guaranteeMoney/actions.html', $pagedata)->render();
    }

    /**
     * @brief 店铺保证金基本详情
     * @param $row
     *
     * @return
     */
    public function detail_basic($row){
        $apiParam = array(
            'shop_id' => intval($row),
            'fields' => 'shop_name,shop_type,cat.cat_name,brand.brand_name',
        );

        $shopInfo =  app::get('sysfinance')->rpcCall('shop.get.detail', $apiParam);
        $guaranteeMoney = $this->objMdlGuranteeMoney->getRow('*', array('shop_id'=>intval($row)));
        $pagedata = $shopInfo;
        $pagedata['guaranteeMoney'] = $guaranteeMoney;

        return view::make('sysfinance/guaranteeMoney/detail.html', $pagedata)->render();

    }

    /**
     * @brief 店铺保证金充值
     * @param $row
     *
     * @return
     */
    public function detail_recharge($row)
    {
        $guaranteeMoney = $this->objMdlGuranteeMoney->getRow('*',array('shop_id'=>$row));
        $pagedata = $guaranteeMoney;
        $pagedata['op_type'] = 'recharge';
        $pagedata['url'] = '?app=desktop&act=alertpages&goto='.urlencode('?app=sysfinance&&ctl=admin_guaranteelog&act=index&shop_id='.$row.'&nobutton=1');
        $pagedata['submit_url'] ='?app=sysfinance&ctl=admin_guaranteeMoney&act=recharge';

        return view::make('sysfinance/guaranteeMoney/edit.html', $pagedata)->render();
   
    }

    /**
     * @brief 店铺保证金扣款
     * @param $row
     *
     * @return
     */
    public function detail_expense($row){
        $guaranteeMoney = $this->objMdlGuranteeMoney->getRow('*',array('shop_id'=>$row));
        $pagedata = $guaranteeMoney;
        $pagedata['op_type'] = 'expense'; 
        $pagedata['url'] = '?app=desktop&act=alertpages&goto='.urlencode('?app=sysfinance&&ctl=admin_guaranteelog&act=index&shop_id='.$row.'&nobutton=1');
        $pagedata['submit_url'] ='?app=sysfinance&ctl=admin_guaranteeMoney&act=expense';

        return view::make('sysfinance/guaranteeMoney/edit.html', $pagedata)->render();
    }

    /**
     * @brief 店铺保证金额度设置
     * @param $row
     *
     * @return
     */
    public function detail_setGuaranteeMoney($row)
    {
        $pagedata = $this->objMdlGuranteeMoney->getRow('*',array('shop_id'=>$row));

        return view::make('sysfinance/guaranteeMoney/setting.html', $pagedata)->render();
    }
}