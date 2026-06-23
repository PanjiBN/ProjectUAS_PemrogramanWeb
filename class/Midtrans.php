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

        // Deteksi apakah berjalan di localhost / development
        $server_name = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
        $is_localhost = in_array($server_name, ['localhost', '127.0.0.1', '::1'])
                     || strpos($server_name, 'localhost') !== false;

        // ── Percepat koneksi: nonaktifkan Expect header (hilangkan 100-Continue delay) ──
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Expect:',
            'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':')
        ];

        $curl_opts = [
            CURLOPT_URL            => MIDTRANS_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => json_encode($payload),

            // ── Timeout dioptimasi: connect 5s, total 15s ──
            // (sebelumnya: connect 10s, total 30s — ini penyebab loading lama!)
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 15,

            // ── Gunakan HTTP/1.1 untuk performa lebih baik ──
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        ];

        // ── Fix SSL untuk localhost XAMPP ──
        // SSL verify di localhost sering gagal & menyebabkan delay besar
        if ($is_localhost) {
            $curl_opts[CURLOPT_SSL_VERIFYPEER] = false;
            $curl_opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        // ── Coba maksimal 2x jika koneksi gagal (retry 1x) ──
        $max_attempts = 2;
        $last_error   = '';

        for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, $curl_opts);

            $response   = curl_exec($ch);
            $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                $last_error = $curl_error;
                if ($attempt < $max_attempts) {
                    usleep(500000); // tunggu 0.5 detik sebelum retry
                    continue;
                }
                throw new Exception("cURL Error saat menghubungi Midtrans: " . $last_error);
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
    }

    public static function verifySignature($order_id, $status_code, $gross_amount, $signature_key) {
        $expected = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);
        return hash_equals($expected, $signature_key);
    }
}
?>
