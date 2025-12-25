<?php

namespace Greelogix\KPayment\Services;

use Illuminate\Support\Facades\Log;
use Greelogix\KPayment\Exceptions\KnetException;
use Greelogix\KPayment\Models\KnetPayment;

class KnetService
{
    protected string $tranportalId;
    protected string $tranportalPassword;
    protected string $resourceKey;
    protected string $baseUrl;
    protected bool $testMode;
    protected string $responseUrl;
    protected string $errorUrl;
    protected string $currency;
    protected string $language;
    protected bool $kfastEnabled;
    protected bool $applePayEnabled;

    public function __construct(
        string $tranportalId,
        string $tranportalPassword,
        string $resourceKey,
        string $baseUrl,
        bool $testMode = true,
        string $responseUrl = '',
        string $errorUrl = '',
        string $currency = '414',
        string $language = 'EN',
        bool $kfastEnabled = false,
        bool $applePayEnabled = false
    ) {
        $this->tranportalId = $tranportalId;
        $this->tranportalPassword = $tranportalPassword;
        $this->resourceKey = $resourceKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->testMode = $testMode;
        $this->responseUrl = $responseUrl;
        $this->errorUrl = $errorUrl;
        $this->currency = $currency;
        $this->language = $language;
        $this->kfastEnabled = $kfastEnabled;
        $this->applePayEnabled = $applePayEnabled;
    }

    /**
     * Get available payment methods from KNET API
     * Attempts to fetch from API, falls back to standard methods
     */
    public function getPaymentMethodsFromApi(string $platform = 'web'): array
    {
        try {
            // KNET API endpoint for payment methods (if available)
            // Note: KNET may not have a dedicated endpoint, this is a placeholder
            $apiUrl = str_replace('/PaymentHTTP.htm', '/api/payment-methods', $this->baseUrl);
            
            $params = [
                'id' => $this->tranportalId,
                'password' => $this->tranportalPassword,
                'platform' => $platform,
            ];

            // Generate hash for API request
            $hashString = $this->generateHashString($params);
            $params['hash'] = $this->generateHash($hashString);

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->asForm()
                ->post($apiUrl, $params);

            if ($response->successful()) {
                $data = $this->parseResponse($response->body());
                
                // If API returns payment methods, format and return them
                if (isset($data['payment_methods']) && is_array($data['payment_methods'])) {
                    return $this->formatPaymentMethods($data['payment_methods'], $platform);
                }
            }
        } catch (\Exception $e) {
            Log::warning('KNET API: Could not fetch payment methods from API', [
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback to standard methods if API call fails
        return $this->getStandardPaymentMethods($platform);
    }

    /**
     * Get available payment methods
     * Returns standard KNET payment methods (can be overridden to use API)
     */
    public function getPaymentMethods(string $platform = 'web'): array
    {
        // Try to get from API first, fallback to standard
        return $this->getPaymentMethodsFromApi($platform);
    }

    /**
     * Get standard payment methods (fallback)
     */
    protected function getStandardPaymentMethods(string $platform = 'web'): array
    {
        // Standard KNET payment methods
        $methods = [
            [
                'code' => 'KNET',
                'name' => 'KNET Card',
                'platform' => ['web', 'ios', 'android'],
            ],
            [
                'code' => 'VISA',
                'name' => 'Visa',
                'platform' => ['web', 'ios', 'android'],
            ],
            [
                'code' => 'MASTERCARD',
                'name' => 'Mastercard',
                'platform' => ['web', 'ios', 'android'],
            ],
        ];

        // Add KFAST if enabled
        if ($this->kfastEnabled) {
            $methods[] = [
                'code' => 'KFAST',
                'name' => 'KFAST',
                'platform' => ['web', 'ios', 'android'],
            ];
        }

        // Add Apple Pay if enabled
        if ($this->applePayEnabled) {
            $methods[] = [
                'code' => 'APPLE_PAY',
                'name' => 'Apple Pay',
                'platform' => ['ios', 'web'],
            ];
        }

        // Filter by platform
        return array_values(array_filter($methods, function ($method) use ($platform) {
            return in_array(strtolower($platform), array_map('strtolower', $method['platform']));
        }));
    }

    /**
     * Format payment methods from API response
     */
    protected function formatPaymentMethods(array $apiMethods, string $platform): array
    {
        $formatted = [];
        
        foreach ($apiMethods as $method) {
            $code = $method['code'] ?? $method['id'] ?? null;
            $name = $method['name'] ?? $method['title'] ?? $code;
            $methodPlatform = $method['platform'] ?? ['web', 'ios', 'android'];
            
            if ($code && in_array(strtolower($platform), array_map('strtolower', (array)$methodPlatform))) {
                $formatted[] = [
                    'code' => strtoupper($code),
                    'name' => $name,
                    'platform' => (array)$methodPlatform,
                ];
            }
        }
        
        return $formatted;
    }

    /**
     * Generate payment form data
     */
    public function generatePaymentForm(array $data): array
    {
        $trackId = $data['track_id'] ?? $this->generateTrackId();
        $amount = number_format((float)($data['amount'] ?? 0), 3, '.', '');

        $params = [
            'id' => $this->tranportalId,
            'password' => $this->tranportalPassword,
            'action' => $data['action'] ?? '1', // 1 = Purchase
            'langid' => $data['language'] ?? $this->language,
            'currencycode' => $data['currency'] ?? $this->currency,
            'amt' => $amount,
            'trackid' => $trackId,
            'responseURL' => $data['response_url'] ?? $this->responseUrl,
            'errorURL' => $data['error_url'] ?? $this->errorUrl,
        ];

        // Store selected payment method in UDF1 if provided
        if (isset($data['payment_method_code'])) {
            $params['udf1'] = $data['payment_method_code'];
        }

        // Add other UDF fields if provided
        for ($i = 1; $i <= 5; $i++) {
            $key = 'udf' . $i;
            if (isset($data[$key]) && !isset($params[$key])) {
                $params[$key] = $data[$key];
            }
        }

        // Generate hash
        $hashString = $this->generateHashString($params);
        $params['hash'] = $this->generateHash($hashString);

        // Create payment record
        $payment = KnetPayment::create([
            'track_id' => $trackId,
            'amount' => $amount,
            'currency' => $params['currencycode'],
            'payment_method' => $data['payment_method_code'] ?? null,
            'status' => 'pending',
            'request_data' => $params,
        ]);

        $params['payment_id'] = $payment->id;

        return [
            'form_url' => $this->baseUrl,
            'form_data' => $params,
            'payment_id' => $payment->id,
            'track_id' => $trackId,
        ];
    }

    /**
     * Validate payment response
     */
    public function validateResponse(array $response): bool
    {
        if (!isset($response['hash'])) {
            return false;
        }

        $receivedHash = $response['hash'];
        unset($response['hash']);

        $hashString = $this->generateHashString($response);
        $calculatedHash = $this->generateHash($hashString);

        return hash_equals($calculatedHash, $receivedHash);
    }

    /**
     * Process payment response
     */
    public function processResponse(array $response): KnetPayment
    {
        if (!$this->validateResponse($response)) {
            throw new KnetException(__('kpayment.response.invalid_hash'));
        }

        $trackId = $response['trackid'] ?? null;
        if (!$trackId) {
            throw new KnetException(__('kpayment.response.track_id_not_found'));
        }

        $payment = KnetPayment::where('track_id', $trackId)->first();
        if (!$payment) {
            throw new KnetException(__('kpayment.response.payment_not_found'));
        }

        $status = $this->determineStatus($response);
        
        $payment->update([
            'payment_id' => $response['paymentid'] ?? null,
            'result' => $response['result'] ?? null,
            'result_code' => $response['result'] ?? null,
            'auth' => $response['auth'] ?? null,
            'ref' => $response['ref'] ?? null,
            'trans_id' => $response['tranid'] ?? null,
            'post_date' => $response['postdate'] ?? null,
            'udf1' => $response['udf1'] ?? null,
            'udf2' => $response['udf2'] ?? null,
            'udf3' => $response['udf3'] ?? null,
            'udf4' => $response['udf4'] ?? null,
            'udf5' => $response['udf5'] ?? null,
            'status' => $status,
            'response_data' => $response,
        ]);

        return $payment;
    }

    /**
     * Process refund
     */
    public function processRefund(array $data): array
    {
        $params = [
            'id' => $this->tranportalId,
            'password' => $this->tranportalPassword,
            'action' => '2', // 2 = Refund
            'transid' => $data['trans_id'], // Original transaction ID
            'trackid' => $data['track_id'] ?? $this->generateTrackId(),
            'amt' => number_format((float)($data['amount'] ?? 0), 3, '.', ''),
        ];

        $hashString = $this->generateHashString($params);
        $params['hash'] = $this->generateHash($hashString);

        // Make refund request (KNET uses HTTP POST for refunds)
        $response = $this->makeRefundRequest($params);

        return $response;
    }

    /**
     * Generate hash string for signature
     */
    protected function generateHashString(array $params): string
    {
        // KNET hash format: resource_key + param1 + param2 + ... + paramN
        $hashString = $this->resourceKey;
        
        // Sort parameters by key
        ksort($params);
        
        foreach ($params as $key => $value) {
            if ($key !== 'hash' && $value !== null && $value !== '') {
                $hashString .= $value;
            }
        }

        return $hashString;
    }

    /**
     * Generate hash
     */
    protected function generateHash(string $hashString): string
    {
        return strtoupper(hash('sha256', $hashString));
    }

    /**
     * Generate unique track ID
     */
    protected function generateTrackId(): string
    {
        return time() . rand(1000, 9999);
    }

    /**
     * Determine payment status from response
     */
    protected function determineStatus(array $response): string
    {
        $result = $response['result'] ?? '';
        
        if (in_array($result, ['CAPTURED', 'SUCCESS'])) {
            return 'success';
        }
        
        if (in_array($result, ['NOT CAPTURED', 'FAILED', 'CANCELLED'])) {
            return 'failed';
        }
        
        return 'pending';
    }

    /**
     * Make refund request
     */
    protected function makeRefundRequest(array $params): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->post($this->baseUrl, $params);

            if (!$response->successful()) {
                throw new KnetException('Refund request failed: ' . $response->body());
            }

            // Parse response (KNET returns form-encoded or XML)
            $responseData = $this->parseResponse($response->body());

            return $responseData;
        } catch (\Exception $e) {
            Log::error('KNET Refund Error', [
                'params' => $params,
                'error' => $e->getMessage(),
            ]);

            throw new KnetException('Refund request failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse KNET response
     */
    protected function parseResponse(string $response): array
    {
        // KNET may return XML or form-encoded data
        if (strpos($response, '<?xml') !== false) {
            $xml = simplexml_load_string($response);
            return json_decode(json_encode($xml), true);
        }

        // Parse form-encoded response
        parse_str($response, $parsed);
        return $parsed;
    }

    /**
     * Get payment by track ID
     */
    public function getPaymentByTrackId(string $trackId): ?KnetPayment
    {
        return KnetPayment::where('track_id', $trackId)->first();
    }

    /**
     * Get payment by transaction ID
     */
    public function getPaymentByTransId(string $transId): ?KnetPayment
    {
        return KnetPayment::where('trans_id', $transId)->first();
    }
}

