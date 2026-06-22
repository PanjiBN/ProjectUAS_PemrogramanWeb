<?php
require_once __DIR__ . '/../config/midtrans_config.php';

class Midtrans {
    public static function createSnapToken($order_id, $gross_amount, $customer = [], $items = []) {
        $payload = [
            'transaction_details' => [
                'order_id'     => $order_id,
                'gross_amount' => (int) $gross_amount
            ]
        ];

        // Customer details 
        if (!empty($customer)) {
            $payload['customer_details'] = [
                'first_name' => $customer['first_name'] ?? 'Customer',
                'email'      => $customer['email'] ?? '',
                'phone'      => $customer['phone'] ?? ''
            ];
        }

        // Item details
        if (!empty($items)) {
            $payload['item_details'] = $items;
        }

        // Panggil API Midtrans untuk mendapatkan snap token
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => MIDTRANS_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new Exception("cURL Error saat menghubungi Midtrans: " . $curl_error);
        }

        $result = json_decode($response, true);

        if ($http_code !== 201 || !isset($result['token'])) {
            $error_msg = $result['error_messages'][0] ?? ($result['message'] ?? 'Unknown error dari Midtrans API');
            throw new Exception("Midtrans API Error [{$http_code}]: " . $error_msg);
        }

        return [
            'snap_token'   => $result['token'],
            'redirect_url' => $result['redirect_url'] ?? ''
        ];
    }
    
    public static function verifySignature($order_id, $status_code, $gross_amount, $signature_key) {
        $expected = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);
        return hash_equals($expected, $signature_key);
    }
}
?>
