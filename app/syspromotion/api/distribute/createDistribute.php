<?php
/**
 * 优惠定向发放
 * promotion.distribute.create
 */
class syspromotion_api_distribute_createDistribute {

    public $apiDescription = "给会员推送红包、购物券";

    public $use_strict_filter = true; // 是否严格过滤参数

    public function getParams()
    {
        $return['params'] = [
            'distribute_name' => ['type'=>'string','valid'=>'required|string|max:50','description'=>'标题'],
            'user_filter' => ['type'=>'string', 'valid'=>'', 'example'=>'', 'description'=>'会员选择器Json' ],
            'discount_type' => ['type'=>'string','valid'=>'required|in:hongbao,voucher','description'=>'使用平台'],
            'discount_param' => ['type'=>'string', 'valid'=>'', 'example'=>'', 'description'=>'优惠的详细数据Json' ],
            'remind_way' => ['type'=>'string','valid'=>'required|in:none,email,sms,both','description'=>'通知途径'],
            'sms_tmpl' => ['type'=>'string', 'valid'=>'', 'example'=>'', 'description'=>'短信模板' ],
            'email_tmpl' => ['type'=>'string', 'valid'=>'', 'example'=>'', 'description'=>'邮件模板' ],
        ];
        return $return;
    }

    public function create()
    {
        $distribute = [];
        $distribute['distribute_name'] = $distribute['distribute_name'];
        $distribute['user_filter']     = json_decode($distribute['user_filter']);
        $distribute['discount_type']   = $distribute['discount_type'];
        $distribute['discount_param']  = json_decode($distribute['discount_param']);
        $distribute['remind_way']      = $distribute['remind_way'];
        $distribute['sms_tmpl']        = $distribute['sms_tmpl'];
        $distribute['email_tmpl']      = $distribute['email_tmpl'];

        return kernel::single('syspromotion_distribute_object')->createDistribute($distribute);
    }

}

