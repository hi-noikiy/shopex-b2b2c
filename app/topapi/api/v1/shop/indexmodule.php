<?php
/**
 * topapi
 *
 * -- shop.index
 * -- 获取店铺首页配置信息
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class topapi_api_v1_shop_indexmodule implements topapi_interface_api{

    /**
     * 接口作用说明
     */
    public $apiDescription = '获取店铺首页配置信息';

    public $orderSort = array(
        'addtime-l' => 'list_time desc',
        'addtime-s' => 'list_time asc',
        'price-l' => 'price desc',
        'price-s' => 'price asc',
        'sell-l' => 'sold_quantity desc',
        'sell-s' => 'sold_quantity asc',
    );

    /**
     * 定义API传入的应用级参数
     * @desc 用于在调用接口前，根据定义的参数，过滤必填参数是否已经参入，并且定义参数的数据类型，参数是否必填，参数的描述
     * @return array 返回传入参数
     */
    public function setParams()
    {
        return [
            'shop_id'   => ['type'=>'int','valid'=>'required|min:1', 'example'=>'1', 'desc'=>'店铺id'],
        ];
        return $return;
    }

    /**
     * @return
     */
    public function handle($params)
    {
        $shopId = $params['shop_id'];

        //流量统计
        $disabled = config::get('stat.disabled');
        if(!$disabled){
            $params['page'] = 'shop';
            $params['page_rel_id'] = $shopId;
            $params['use_platform'] = 'wap';
            $params['shop_id'] = $shopId;
            $params['remote_addr'] = $_SERVER['REMOTE_ADDR'];
            app::get('sysstat')->rpcCall('sysstat.traffic.data.create',$params);
        }

        // 店铺分类
        $shopcat = app::get('topapi')->rpcCall('shop.cat.get',array('shop_id'=>$shopId,'parent_id'=>0));
        $pagedata['shopcat'] = array_values($shopcat);//自然索引
        $pagedata['shopId'] = $shopId;

        $topshopNewSetup = app::get('sysconf')->getConf('topshop.firstSetup');
        $openNewAppdecorate = redis::scene('shopDecorate')->hget('appdecorate_status','shop_'.$shopId);
        //如果开启APP新店铺装修，显示新店铺装修内容
        if($topshopNewSetup || $openNewAppdecorate =='open')
        {
            $pagedata = app::get('topapi')->rpcCall('shop.newIndex',['shop_id'=>$shopId]);
            if($pagedata['widgets'])
            {
                foreach ($pagedata['widgets'] as $k => $v) {
                    if($v['widgets_type'] =='slider'){
                        $pagedata['widgets'][$k]['slider_first_image'] = reset($v['params']);
                        $pagedata['widgets'][$k]['slider_last_image'] = end($v['params']);
                    }

                    if($v['widgets_type'] == 'goods') {
                        $filter = array(
                            'shop_id' => $v['shop_id'],
                            'item_id' => implode(',', $v['params']['item_id']),
                        );

                        $pagedata['widgets'][$k]['showitems'] = $this->__getwidgetsShowItems($filter);
                        $pagedata['widgets'][$k]['itemIds'] = implode(',', $v['params']['item_id']);
                    }
                }
            }

            return $pagedata;
        }

        $pagedata = $this->__common($shopId);

        //店铺关闭后跳转至关闭页面
        if($pagedata['shopdata']['status'] == "dead")
        {
            return $pagedata;
        }

        // 店铺优惠券信息,
        $params = array(
            'page_no' => 0,
            'page_size' => 10,
            'fields' => 'deduct_money,coupon_name,coupon_id,shop_id',
            'shop_id' => $shopId,
            'platform' => 'wap',
            'is_cansend' => 1,
        );
        $couponListData = app::get('topapi')->rpcCall('promotion.coupon.list', $params, 'buyer');
        $pagedata['homeCouponList']= $couponListData['coupons'] ?: null;

        //店铺商品展示
        $showItems = shopWidgets::getWapInfo('wapshowitems',$shopId);
        $pagedata['showitems'] = array_values($this->__getShowItems($showItems));

        //自定义广告
        $custom = shopWidgets::getWapInfo('wapcustom', $shopId);
        $custom = $custom[0]['params']['custom'];
        $pagedata['custom'] = $custom;

        return $pagedata;
    }

    /**
     * 获取店铺模板页面头部共用部分的数据
     *
     * @param int $shopId 店铺ID
     * @return array
     */
    private function __common($shopId)
    {
        $shopId = intval($shopId);

        //店铺招牌背景色
        $wapslider = shopWidgets::getWapInfo('waplogo',$shopId);
        $commonData['logo_image'] = $wapslider[0]['params'];
        // 处理图片链接
        if($commonData['logo_image']['shop_logo'])
        {
            $commonData['logo_image']['shop_logo'] = base_storager::modifier($commonData['logo_image']['shop_logo']);
        }
        //店铺信息
        $shopdata = app::get('topapi')->rpcCall('shop.get',array('shop_id'=>$shopId));
        if($shopdata['status'] == "dead")
        {
            return ['shopdata'=>$shopdata, 'logo_image'=>$commonData['logo_image']];
        }
        // 处理图片链接
        $shopdata['shop_logo'] = base_storager::modifier($shopdata['shop_logo'], 't');
        // h5端链接
        $shopdata['h5href'] = url::action('topwap_ctl_shop@index', ['shop_id'=>$shopId]);
        $commonData['shopdata'] = $shopdata;


        $params['shop_id'] = $shopId;
        $params['catDsrDiff'] = false;
        $params['countNum'] = false;
        $dsrData = app::get('topwap')->rpcCall('rate.dsr.get', $params);
        if( !$dsrData )
        {
            $commonData['tally_dsr'] = sprintf('%.1f',5.0);
            $commonData['attitude_dsr'] = sprintf('%.1f',5.0);
            $commonData['delivery_speed_dsr'] = sprintf('%.1f',5.0);
        }
        else
        {
            $commonData['tally_dsr'] = sprintf('%.1f',$dsrData['tally_dsr']);
            $commonData['attitude_dsr'] = sprintf('%.1f',$dsrData['attitude_dsr']);
            $commonData['delivery_speed_dsr'] = sprintf('%.1f',$dsrData['delivery_speed_dsr']);
        }

        //店铺论播广告
        $wapslider = shopWidgets::getWapInfo('wapslider',$shopId);
        $commonData['slider'] = array_values($wapslider[0]['params']);

        //标签展示
        $itemList = shopWidgets::getWapInfo('waptags',$shopId);
        $commonData['itemInfo'] = array_values($this->__getItemInfo($itemList));

        return $commonData;
    }

    //获取标签
    private function __getItemInfo($data)
    {
        $sort = unserialize(app::get('topshop')->getConf('wap_decorate.tagSort'));
        foreach ($data as $key => $value)
        {
            if($value['params']['isstart'])
            {
                $value['params']['item_id'] = implode(',',$value['params']['item_id']);
                $itemData[$value['widgets_id']] = $value;
                $itemData[$value['widgets_id']]['order_sort'] = $sort[$value['widgets_id']]['order_sort'];
            }
        }
        $items = $this->array_sort($itemData,'order_sort');

        return $items;
    }

    //获取商品
    private function __getShowItems($data)
    {
        $sort = unserialize(app::get('topshop')->getConf('wap_decorate.showItemSort'));
        foreach ($data as $key => $value)
        {
            if($value['params']['isstart'])
            {
                $itemData[$value['widgets_id']] = $value;
                $params=array('shop_id'=>$value['shop_id'],'use_platform'=>'0');
                $params['orderBy'] = $this->orderSort[$value['params']['ordersort']];
                $params['page_size'] = $value['params']['itemlimit'];
                $params['pages'] = 1;
                $params['item_id'] = implode(',',$value['params']['item_id']);
                $itemsList = kernel::single('topapi_item_search')->setLimit($params['page_size'])
                    ->search($params)
                    ->setItemsActivetyTag()
                    ->setItemsPromotionTag()
                    ->getData();

                $itemData[$value['widgets_id']]['params']['item_id'] = $params['item_id'];
                $itemData[$value['widgets_id']]['params']['itemlist'] = $itemsList;
                $itemData[$value['widgets_id']]['order_sort'] = $sort[$value['widgets_id']]['order_sort'];
            }
        }
        $items = $this->array_sort($itemData,'order_sort');
        return $items;
    }
    //排序
    private function array_sort($arr,$keys,$type='asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v)
        {
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc')
        {
            asort($keysvalue);
        }
        else
        {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v)
        {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    //新店铺装修获取商品
    private function __getwidgetsShowItems($filter)
    {
        $params['shop_id'] = $filter['shop_id'];
        $params['item_id'] = $filter['item_id'];
        $params['page_size'] = $filter['page_size'];
        $params['pages'] = $filter['pages'] ? $filter['pages'] : 1;

        $itemsList = kernel::single('topapi_item_search')->setLimit($params['page_size'])
                    ->search($params)
                    ->setItemsActivetyTag()
                    ->setItemsPromotionTag()
                    ->getData();

        return $itemsList;
    }

    /**
     * 返回json格式的例子
     * @return string 结果json串
     */
    public function returnJson()
    {
        return '{"errorcode":0,"msg":"","data":{"logo_image":{"show_shop_logo":"on","shop_logo":"http://images.bbc.shopex123.com/images/50/f8/ef/a0b4cbdcbcb45bcf75ebd8ee7e502de9ec44903e.png"},"shopdata":{"shop_id":3,"shop_name":"onexbbc自营店（自营店铺）","shop_descript":"onexbbc自营体验店","shop_type":"self","status":"active","open_time":1453699800,"qq":"","wangwang":"","shop_logo":"http://images.bbc.shopex123.com/images/e4/64/42/37eff0aeba30e897184f510c248deebd79cff488.png_t.png","shop_area":"上海","shop_addr":"桂林路396号","mobile":"13788822222","shopname":"onexbbc自营店（自营店铺）自营店","shoptype":"运营商自营"},"tally_dsr":"5.0","attitude_dsr":"5.0","delivery_speed_dsr":"5.0","slider":[{"sliderImage":"http://images.bbc.shopex123.com/images/ed/ec/09/0e40b2897438d4cb7ffac23cdad33f45adabfb55.png","link":""}],"itemInfo":[{"widgets_id":43,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"ONLY","item_id":"22,23,24,25,26","itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null},{"widgets_id":42,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"迪士尼","item_id":"60,61,63,65,66,72,73,83","itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null},{"widgets_id":41,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"ETAM","item_id":"125,126,127,128,129,130","itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null},{"widgets_id":40,"shop_id":3,"widgets_type":"waptags","params":{"tagsname":"ASICS","item_id":"131,132,133,134","itemlimit":20,"isstart":1,"ordersort":null},"modified_time":1453894095,"order_sort":null}],"shopId":"3","homeCouponList":[{"deduct_money":"15.000","coupon_name":"连衣裙 满100减15","coupon_id":13,"shop_id":3},{"deduct_money":"20.000","coupon_name":"智能设备类 满500减20","coupon_id":12,"shop_id":3},{"deduct_money":"5.000","coupon_name":"满100减5","coupon_id":11,"shop_id":3},{"deduct_money":"100.000","coupon_name":"满1000减100","coupon_id":10,"shop_id":3}],"shopcat":[{"cat_id":21,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"女装","order_sort":0,"modified_time":1472813932,"disabled":0},{"cat_id":52,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"男装","order_sort":2,"modified_time":1472813932,"disabled":0},{"cat_id":29,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"家电数码","order_sort":4,"modified_time":1472813932,"disabled":0},{"cat_id":35,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"母婴","order_sort":7,"modified_time":1472813932,"disabled":0},{"cat_id":55,"shop_id":3,"parent_id":0,"cat_path":",","level":"1","is_leaf":0,"cat_name":"Shopex","order_sort":13,"modified_time":1472813932,"disabled":0}],"showitems":[{"widgets_id":46,"shop_id":3,"widgets_type":"wapshowitems","params":{"tagsname":"女装","item_id":"81,80,79,22,23,24,25,26,123,122,128,130","itemlimit":"6","isstart":"1","ordersort":"addtime-l","itemlist":{"list":[{"item_id":26,"title":"ONLY春季新品条纹彼得潘领包臀五分袖连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/74/32/fc/414d7236e416eb52ebd17b2e30f8d1f0fb85838c.jpg_s.jpg","price":"249.000","sold_quantity":1,"promotion":[],"gift":null},{"item_id":128,"title":"艾格 ETAM 彩色数码印花无袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/44/4b/d5/332d15a9ddfd0434a19cbddb374101527100b2af.png_s.png","price":"224.000","sold_quantity":2,"promotion":{"12":{"item_id":128,"promotion_id":12},"13":{"item_id":128,"promotion_id":13},"14":{"item_id":128,"promotion_id":14},"15":{"item_id":128,"promotion_id":15}},"gfit":{"gift_id":1,"promotion_tag":"赠品"}},{"item_id":130,"title":"etam艾格时尚修身连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/5d/7c/23/aa461ffb145e94922c1036e5aa8edbbb138f1bf6.png_s.png","price":"149.000","sold_quantity":0,"promotion":[],"gift":null},{"item_id":123,"title":"拼接微透长袖连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/6d/6e/23/5d1dede8b5575c7f41ee6b865278b437ad9f3173.png_s.png","price":"654.000","sold_quantity":0,"promotion":[],"gift":null},{"item_id":122,"title":"夏季新品欧美风圆领无袖女式网眼镂空雪纺连衣裙","image_default_id":"http://images.bbc.shopex123.com/images/48/a6/10/eafdb9c20e9607486befbfac23fcffa16a8a5c35.png_s.png","price":"499.000","sold_quantity":1,"promotion":[],"gfit":{"gift_id":1,"promotion_tag":"赠品"}},{"item_id":24,"title":"ONLY秋装新品厚实针织显瘦七分袖修身连衣裙女","image_default_id":"http://images.bbc.shopex123.com/images/a3/18/9d/d1897d3766862b7d5515b0c2aa4eea06042d98fb.jpg_s.jpg","price":"299.000","sold_quantity":4,"promotion":[],"gfit":{"gift_id":1,"promotion_tag":"赠品"}}]}},"modified_time":1453894266,"order_sort":null},{"widgets_id":45,"shop_id":3,"widgets_type":"wapshowitems","params":{"tagsname":"手机","item_id":"31,33,35,39,53,82,84,90,135,136","itemlimit":"6","isstart":"1","ordersort":"addtime-l","itemlist":{"list":[{"item_id":31,"title":"微软(Microsoft) Lumia 640XL LTE ","image_default_id":"http://images.bbc.shopex123.com/images/7d/24/1e/23f41b46db82094677ea853d091bfbd916f62d1d.jpg_s.jpg","price":"1299.000","sold_quantity":0,"gift":null,"promotion":[]},{"item_id":135,"title":"索尼(SONY) E6883 Xperia Z5尊享版 移动","image_default_id":"http://images.bbc.shopex123.com/images/2e/70/24/85a359d40c6878e553b358337c07392f7ea120f6.png_s.png","price":"5699.000","sold_quantity":1,"gift":null,"promotion":[]},{"item_id":136,"title":"Sony/索尼 E5823 Xperia Z5 Compac","image_default_id":"http://images.bbc.shopex123.com/images/1d/2c/97/c03ed485e7dbbe83622e261d3109b4accfcf28c4.jpg_s.jpg","price":"3488.000","sold_quantity":0,"gift":null,"promotion":[]},{"item_id":90,"title":"华为M2平板电脑8英寸M2-801w/803L LTE通话4","image_default_id":"http://images.bbc.shopex123.com/images/46/86/b6/c5a958bc1b03030eee1bf079d80d05cb7aab0606.png_s.png","price":"2289.000","sold_quantity":0,"gift":null,"promotion":[]},{"item_id":84,"title":"LG G4（H818）闪耀金 国际版 移动联通双4G手机 ","image_default_id":"http://images.bbc.shopex123.com/images/0b/eb/fe/ab193c51f360c72b0bd1f1d6bfb0a2d9e0c733c1.png_s.png","price":"2699.000","sold_quantity":2,"gfit":{"gift_id":1,"promotion_tag":"赠品"},"promotion":[]},{"item_id":82,"title":"魅族 魅蓝metal 32GB 蓝色 ","image_default_id":"http://images.bbc.shopex123.com/images/38/c7/2b/f7abd93395b3b6636a2aea587e885a269224c4b4.png_s.png","price":"1199.000","sold_quantity":1,"gift":null,"promotion":[]}]}},"modified_time":1453894181,"order_sort":null},{"widgets_id":44,"shop_id":3,"widgets_type":"wapshowitems","params":{"tagsname":"童装","item_id":"70,68,67,66,65,63,61,60","itemlimit":"6","isstart":"1","ordersort":"addtime-l","itemlist":{"list":[{"item_id":70,"title":"纳兰小猪 童装男童加绒加厚卫衣","image_default_id":"http://images.bbc.shopex123.com/images/c1/01/90/a6fe2e777efa177fafe19e31c48dfb1e4cbcb913.png_s.png","price":"59.000","sold_quantity":0,"gift":null,"activity":{"activity_id":3,"item_id":70,"activity_tag":"平台活动","price":"59.000","activity_price":"20.000"},"promotion":[]},{"item_id":68,"title":"纳兰小猪童装男童卫衣加厚冬款 中大儿童加绒套头卫衣","image_default_id":"http://images.bbc.shopex123.com/images/89/65/45/220308767e11239cdd860754a6621536780f46d3.png_s.png","price":"59.000","sold_quantity":2,"gfit":{"gift_id":1,"promotion_tag":"赠品"},"activity":{"activity_id":3,"item_id":68,"activity_tag":"平台活动","price":"59.000","activity_price":"19.000"},"promotion":[]},{"item_id":63,"title":"迪士尼男童女童摇粒绒外套儿童开衫上衣 2016春装","image_default_id":"http://images.bbc.shopex123.com/images/fc/dc/bf/939d8ba149e2f74b5521b1514b6ffc8d179cbf2d.png_s.png","price":"89.000","sold_quantity":0,"gift":null,"activity":{"activity_id":3,"item_id":63,"activity_tag":"平台活动","price":"89.000","activity_price":"66.000"},"promotion":[]},{"item_id":65,"title":"迪士尼女童简约百搭翻领打底衫 2015冬季新款 白","image_default_id":"http://images.bbc.shopex123.com/images/c4/cc/d3/b1d3a500709ae3717b8879bcc2b9938864405e54.png_s.png","price":"58.000","sold_quantity":0,"gift":null,"activity":{"activity_id":3,"item_id":65,"activity_tag":"平台活动","price":"58.000","activity_price":"42.000"},"promotion":[]},{"item_id":66,"title":"迪士尼 可爱印花百搭时尚短袖T恤 蓝绿","image_default_id":"http://images.bbc.shopex123.com/images/ac/d0/3e/dd9932da2606f6984d348af9a2c8e036ef956fe0.png_s.png","price":"39.000","sold_quantity":0,"gift":null,"activity":{"activity_id":3,"item_id":66,"activity_tag":"平台活动","price":"39.000","activity_price":"25.000"},"promotion":[]},{"item_id":67,"title":"纳兰小猪童装男童衬衫加绒加厚中大儿童长袖秋装2015新款衬衣","image_default_id":"http://images.bbc.shopex123.com/images/fc/04/43/c7d244e2c104d6d2f40b9064aab188411099134d.png_s.png","price":"50.000","sold_quantity":0,"gift":null,"activity":{"activity_id":3,"item_id":67,"activity_tag":"平台活动","price":"50.000","activity_price":"39.990"},"promotion":[]}]}},"modified_time":1453894164,"order_sort":null}],"imageSlider":null,"custom":""}}';
    }

}
