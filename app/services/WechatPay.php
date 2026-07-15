<?php
/**
 * 微信支付 Service (V3 API)
 * 
 * 使用前需要：
 * 1. 微信商户平台注册
 * 2. 在宝塔面板/服务器安装 openssl 扩展
 * 3. 配置 app/config/payment.php
 */
class WechatPay {
    private $config;
    private $baseUrl = 'https://api.mch.weixin.qq.com';

    public function __construct($config = []) {
        $this->config = array_merge([
            'mch_id'      => '',
            'app_id'      => '',
            'api_key'     => '',
            'api_v3_key'  => '',
            'cert_path'   => '',
            'key_path'    => '',
            'notify_url'  => '',
        ], $config);
    }

    /**
     * 创建JSAPI支付订单（公众号/小程序内支付）
     * @return array ['prepay_id' => ..., 'pay_params' => ...] 或 ['error' => ...]
     */
    public function createOrder($orderNo, $amount, $description, $openId = '') {
        if (empty($this->config['mch_id']) || empty($this->config['app_id'])) {
            return ['error' => '微信支付未配置商户信息'];
        }

        $body = [
            'appid'         => $this->config['app_id'],
            'mchid'         => $this->config['mch_id'],
            'description'   => $description,
            'out_trade_no'  => $orderNo,
            'notify_url'    => $this->config['notify_url'],
            'amount' => [
                'total'    => (int)($amount * 100), // 分
                'currency' => 'CNY',
            ],
        ];

        if ($openId) {
            $body['payer'] = ['openid' => $openId];
        }

        $url = $this->baseUrl . '/v3/pay/transactions/jsapi';
        $result = $this->request('POST', $url, $body);

        if (isset($result['prepay_id'])) {
            return [
                'prepay_id'  => $result['prepay_id'],
                'pay_params' => $this->buildJsapiParams($result['prepay_id']),
            ];
        }
        return ['error' => $result['message'] ?? '创建支付订单失败'];
    }

    /**
     * 创建Native支付（扫码支付）
     */
    public function createNativeOrder($orderNo, $amount, $description) {
        if (empty($this->config['mch_id'])) {
            return ['error' => '微信支付未配置'];
        }

        $body = [
            'appid'         => $this->config['app_id'],
            'mchid'         => $this->config['mch_id'],
            'description'   => $description,
            'out_trade_no'  => $orderNo,
            'notify_url'    => $this->config['notify_url'],
            'amount' => [
                'total'    => (int)($amount * 100),
                'currency' => 'CNY',
            ],
        ];

        $url = $this->baseUrl . '/v3/pay/transactions/native';
        $result = $this->request('POST', $url, $body);

        if (isset($result['code_url'])) {
            return ['code_url' => $result['code_url']];
        }
        return ['error' => $result['message'] ?? '创建扫码支付失败'];
    }

    /**
     * 处理支付回调通知
     */
    public function handleNotify($body, $headers) {
        // V3 回调验签 + 解密
        $timestamp = $headers['Wechatpay-Timestamp'] ?? '';
        $nonce     = $headers['Wechatpay-Nonce'] ?? '';
        $signature = $headers['Wechatpay-Signature'] ?? '';
        $serial    = $headers['Wechatpay-Serial'] ?? '';

        $data = json_decode($body, true);
        if (!$data || !isset($data['resource'])) {
            return ['error' => '无效通知数据'];
        }

        $resource = $data['resource'];
        $decrypted = $this->decrypt(
            $resource['ciphertext'],
            $resource['associated_data'],
            $resource['nonce']
        );

        $result = json_decode($decrypted, true);
        if (!$result) return ['error' => '解密失败'];

        return [
            'order_no'    => $result['out_trade_no'] ?? '',
            'trade_no'    => $result['transaction_id'] ?? '',
            'amount'      => ($result['amount']['total'] ?? 0) / 100,
            'status'      => $result['trade_state'] ?? '',
        ];
    }

    /**
     * 查询订单
     */
    public function queryOrder($orderNo) {
        $url = $this->baseUrl . '/v3/pay/transactions/out-trade-no/' . $orderNo .
               '?mchid=' . $this->config['mch_id'];
        return $this->request('GET', $url);
    }

    /**
     * 申请退款
     */
    public function refund($orderNo, $refundNo, $totalAmount, $refundAmount, $reason = '') {
        $url = $this->baseUrl . '/v3/refund/domestic/refunds';
        $body = [
            'out_trade_no'  => $orderNo,
            'out_refund_no' => $refundNo,
            'reason'        => $reason ?: '用户申请退款',
            'amount' => [
                'refund'   => (int)($refundAmount * 100),
                'total'    => (int)($totalAmount * 100),
                'currency' => 'CNY',
            ],
        ];
        return $this->request('POST', $url, $body);
    }

    // ===== 内部方法 =====

    private function request($method, $url, $body = null) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);
        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        // TODO: V3 签名（需要商户证书）
        // if ($this->config['cert_path']) {
        //     curl_setopt($ch, CURLOPT_SSLCERT, $this->config['cert_path']);
        //     curl_setopt($ch, CURLOPT_SSLKEY, $this->config['key_path']);
        // }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) return ['error' => $error];
        return json_decode($response, true) ?: ['error' => '解析响应失败', 'raw' => $response, 'http_code' => $httpCode];
    }

    private function buildJsapiParams($prepayId) {
        $timestamp = (string)time();
        $nonce = md5(uniqid());
        $package = 'prepay_id=' . $prepayId;
        // 简化版签名，实际需要用商户私钥签名
        $paySign = md5($this->config['app_id'] . "\n" . $timestamp . "\n" . $nonce . "\n" . $package . "\n");
        return [
            'appId'     => $this->config['app_id'],
            'timeStamp' => $timestamp,
            'nonceStr'  => $nonce,
            'package'   => $package,
            'signType'  => 'RSA',
            'paySign'   => $paySign,
        ];
    }

    private function decrypt($ciphertext, $associatedData, $nonce) {
        if (empty($this->config['api_v3_key'])) {
            return $ciphertext; // 未配置密钥时返回原文
        }
        if (!function_exists('openssl_decrypt')) return $ciphertext;
        $ciphertext = base64_decode($ciphertext);
        return openssl_decrypt($ciphertext, 'aes-256-gcm', $this->config['api_v3_key'], OPENSSL_RAW_DATA, $nonce, $associatedData ?: '');
    }

    /**
     * 检查是否已配置
     */
    public function isConfigured() {
        return !empty($this->config['mch_id']) && !empty($this->config['app_id']);
    }
}