<?php
/**
 * 支付配置
 */
return [
    'wechat' => [
        'mch_id'      => '',
        'app_id'      => '',
        'api_key'     => '',
        'api_v3_key'  => '',
        'cert_path'   => '',
        'key_path'    => '',
        'notify_url'  => 'https://www.xgjia.com/pay/notify/wechat',
    ],
    'alipay' => [
        'app_id'      => '',
        'private_key' => '',
        'alipay_key'  => '',
        'notify_url'  => 'https://www.xgjia.com/pay/notify/alipay',
        'return_url'  => 'https://www.xgjia.com/user/membership',
        'sandbox'     => false,
    ],
    'site_name'   => '信鸽之家',
    'currency'    => 'CNY',
];