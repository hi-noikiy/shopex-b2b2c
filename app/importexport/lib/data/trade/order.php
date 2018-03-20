<?php
/**
 * trade.php
 *
 * @copyright  Copyright (c) 2005-2015 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class importexport_data_trade_order {

    public function get_content_row($row)
    {
        if( $row['gift_data'] ) {
            $data = $row;
            $data['gift_data'] = '否';
            $result['list'][] = $data;
            foreach( $row['gift_data'] as $value )
            {
                $data['item_id']   = $value['title'];
                $data['sku_id']    = $value['title'];
                $data['bn']        = $value['bn'];
                $data['title']     = $value['title'];
                $data['spec_nature_info'] = $value['spec_info'];
                $data['price']     = 0;
                $data['num']       = $value['gift_num'];

                $data['divide_order_fee']           = 0;
                $data['part_mjz_discount']          = 0;
                $data['voucher_id']                 = 0;
                $data['voucher_discount']           = 0;
                $data['voucher_subsidy_proportion'] = 0;

                $data['total_fee']         = 0;
                $data['payment']           = 0;
                $data['points_fee']        = 0;
                $data['consume_point_fee'] = 0;
                $data['total_weight']      = 0;
                $data['discount_fee']      = 0;
                $data['adjust_fee']        = 0;
                $data['refund_fee']        = 0;
                $data['pic_path']          = $value['image_default_id'];
                $data['sub_stock']         = $value['sub_stock'];
                $data['gift_data']         = '赠品';
                $result['list'][]          = $data;
            }
        }
        else
        {
            $row['gift_data'] = '否';
            $result = $row;
        }

        return $result;
    }
}
