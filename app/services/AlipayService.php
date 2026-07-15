<?php
/**
 * 支付宝支付 Service
 * 
 * 使用前需要：
 * 1. 支付宝开放平台创建应用
 * 2. 获取 APPID + 商户私钥 + 支付宝公钥
 * 3. 配置 app/config/payment.php
 */
class AlipayService {
    private $config;
    private $gateway = 'https://openapi.alipay.com/gateway.do';

    public function __construct($config = []) {
        $this->config = array_merge([
            'app_id'        => '',
            'private_key'   => '',
            'alipay_key'    => '',
            'notify_url'    => '',
            'return_url'    => '',
            'sandbox'       => false,
        ], $config);

        if ($this->config['sandbox']) {
            $this->gateway = 'https://openapi-sandbox.dl.alipaydev.com/gateway.do';
        }
    }

    /**
     * 创建PC网站支付
     * @return string HTML表单（自动提交到支付宝）
     */
    public function createPagePay($orderNo, $amount, $subject, $body = '') {
        if (empty($this->config['app_id'])) {
            return '<p style="color:red;">支付宝支付未配置</p>';
        }

        $params = [
            'app_id'      => $this->config['app_id'],
            'method'      => 'alipay.trade.page.pay',
            'format'      => 'JSON',
            'return_url'  => $this->config['return_url'],
            'notify_url'  => $this->config['notify_url'],
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode([
                'out_trade_no' => $orderNo,
                'total_amount' => (string)$amount,
                'subject'      => $subject,
                'body'         => $body ?: $subject,
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
                'timeout_express' => '30m',
            ]),
        ];

        $params['sign'] = $this->generateSign($params);
        return $this->buildForm($params);
    }

    /**
     * 创建手机网站支付
     */
    public function createWapPay($orderNo, $amount, $subject, $body = '') {
        if (empty($this->config['app_id'])) {
            return ['error' => '支付宝支付未配置'];
        }

        $params = [
            'app_id'      => $this->config['app_id'],
            'method'      => 'alipay.trade.wap.pay',
            'format'      => 'JSON',
            'return_url'  => $this->config['return_url'],
            'notify_url'  => $this->config['notify_url'],
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode([
                'out_trade_no' => $orderNo,
                'total_amount' => (string)$amount,
                'subject'      => $subject,
                'body'         => $body ?: $subject,
                'product_code' => 'QUICK_WAP_WAY',
                'timeout_express' => '30m',
            ]),
        ];

        $params['sign'] = $this->generateSign($params);
        return $this->buildForm($params);
    }

    /**
     * 处理异步通知回调
     */
    public function handleNotify($params) {
        $sign = $params['sign'] ?? '';
        unset($params['sign'], $params['sign_type']);
        
        if (!$this->verifySign($params, $sign)) {
            return ['error' => '验签失败'];
        }

        return [
            'order_no'   => $params['out_trade_no'] ?? '',
            'trade_no'   => $params['trade_no'] ?? '',
            'amount'     => (float)($params['total_amount'] ?? 0),
            'status'     => $params['trade_status'] ?? '',
            'raw'        => $params,
        ];
    }

    /**
     * 查询订单
     */
    public function queryOrder($orderNo) {
        $params = [
            'app_id'      => $this->config['app_id'],
            'method'      => 'alipay.trade.query',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode(['out_trade_no' => $orderNo]),
        ];
        $params['sign'] = $this->generateSign($params);
        return $this->executeApi($params);
    }

    /**
     * 申请退款
     */
    public function refund($orderNo, $refundNo, $refundAmount, $reason = '') {
        $params = [
            'app_id'      => $this->config['app_id'],
            'method'      => 'alipay.trade.refund',
            'format'      => 'JSON',
            'charset'     => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'     => '1.0',
            'biz_content' => json_encode([
                'out_trade_no'   => $orderNo,
                'out_request_no' => $refundNo,
                'refund_amount'  => (string)$refundAmount,
                'refund_reason'  => $reason ?: '用户申请退款',
            ]),
        ];
        $params['sign'] = $this->generateSign($params);
        return $this->executeApi($params);
    }

    /**
     * 获取回调成功响应
     */
    public function successResponse() {
        return 'success';
    }

    public function isConfigured() {
        return !empty($this->config['app_id']) && !empty($this->config['private_key']);
    }

    // ===== 内部方法 =====

    private function generateSign($params) {
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            if ($v === '' || $v === null) continue;
            $str .= "$k=$v&";
        }
        $str = rtrim($str, '&');

        if (!empty($this->config['private_key']) && function_exists('openssl_sign')) {
            $key = "-----BEGIN RSA PRIVATE KEY-----\n" .
                   chunk_split($this->config['private_key'], 64, "\n") .
                   "-----END RSA PRIVATE KEY-----";
            openssl_sign($str, $sign, $key, OPENSSL_ALGO_SHA256);
            return base64_encode($sign);
        }
        // Fallback: MD5签名（测试用）
        return md5($str . ($this->config['alipay_key'] ?? ''));
    }

    private function verifySign($params, $sign) {
        // 简化验签，生产环境需用支付宝公钥
        $expected = $this->generateSign($params);
        return hash_equals($expected, $sign);
    }

    private function buildForm($params) {
        $html = '<form id="alipaysubmit" action="' . htmlspecialchars($this->gateway) . '" method="POST">';
        foreach ($params as $key => $value) {
            $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
        $html .= '<input type="submit" value="正在跳转支付宝..." style="display:none;">';
        $html .= '<script>document.getElementById("alipaysubmit").submit();</script>';
        $html .= '</form>';
        return $html;
    }

    private function executeApi($params) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->gateway,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $respKey = str_replace('.', '_', $params['method']) . '_response';
        return $data[$respKey] ?? ['error' => '解析响应失败', 'raw' => $response];
    }
}