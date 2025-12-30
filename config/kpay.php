<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tranportal ID
    |--------------------------------------------------------------------------
    |
    | Your KNET tranportal ID provided by your acquiring bank.
    |
    | IMPORTANT: For TEST MODE, this can be left empty. KNET test environment
    | does not require credentials for testing.
    |
    | For PRODUCTION, this is REQUIRED and must be provided by your acquiring bank.
    |
    | Configure via .env or config file.
    |
    */
    'tranportal_id' => env('KPAY_TRANPORTAL_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Tranportal Password
    |--------------------------------------------------------------------------
    |
    | Your KNET tranportal password provided by your acquiring bank.
    |
    | IMPORTANT: For TEST MODE, this can be left empty. KNET test environment
    | does not require credentials for testing.
    |
    | For PRODUCTION, this is REQUIRED and must be provided by your acquiring bank.
    |
    | Configure via .env or config file.
    |
    */
    'tranportal_password' => env('KPAY_TRANPORTAL_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Resource Key
    |--------------------------------------------------------------------------
    |
    | Your KNET resource key for payment processing.
    |
    | IMPORTANT: For TEST MODE, this can be left empty. KNET test environment
    | does not require credentials for testing.
    |
    | For PRODUCTION, this is REQUIRED and must be provided by your acquiring bank.
    |
    | Configure via .env or config file.
    |
    */
    'resource_key' => env('KPAY_RESOURCE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | KNET Payment Gateway base URL.
    | Test: https://kpaytest.com.kw/kpg/PaymentHTTP.htm
    | Production: https://www.kpay.com.kw/kpg/PaymentHTTP.htm
    |
    | Configure via .env or config file.
    |
    */
    'base_url' => env('KPAY_BASE_URL', 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm'),

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | Set to true for test mode, false for production.
    |
    | IMPORTANT: In test mode, KNET does NOT require any credentials
    | (Tranportal ID, Password, or Resource Key). You can test payments
    | without configuring these fields.
    |
    | Configure via .env or config file.
    |
    */
    'test_mode' => env('KPAY_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Response URL
    |--------------------------------------------------------------------------
    |
    | URL where KNET will redirect after payment processing.
    | 
    | IMPORTANT: This is REQUIRED by KNET. Must be a publicly accessible
    | absolute URL (e.g., https://yoursite.com/kpay/response).
    | 
    | The package provides a default route at /kpay/response, so you can use:
    | https://yoursite.com/kpay/response
    |
    */
    'response_url' => env('KPAY_RESPONSE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Error URL
    |--------------------------------------------------------------------------
    |
    | URL where KNET will redirect on payment errors.
    | 
    | IMPORTANT: This is REQUIRED by KNET. Must be a publicly accessible
    | absolute URL (e.g., https://yoursite.com/kpay/response).
    | 
    | You can use the same URL as response_url, or a different error page.
    | The package provides a default route at /kpay/response, so you can use:
    | https://yoursite.com/kpay/response
    |
    */
    'error_url' => env('KPAY_ERROR_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Default currency code (ISO 4217).
    |
    */
    'currency' => env('KPAY_CURRENCY', '414'), // 414 = KWD

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | Default language code (AR or EN).
    |
    */
    'language' => env('KPAY_LANGUAGE', 'EN'),

    /*
    |--------------------------------------------------------------------------
    | Action
    |--------------------------------------------------------------------------
    |
    | Transaction action code.
    | 1 = Purchase
    | 2 = Refund
    |
    */
    'action' => env('KPAY_ACTION', '1'),

    /*
    |--------------------------------------------------------------------------
    | KFAST Enabled
    |--------------------------------------------------------------------------
    |
    | Enable KFAST (KNET Fast Payment) support.
    |
    */
    'kfast_enabled' => env('KPAY_KFAST_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Apple Pay Enabled
    |--------------------------------------------------------------------------
    |
    | Enable Apple Pay support.
    |
    */
    'apple_pay_enabled' => env('KPAY_APPLE_PAY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Apple Pay Certificate
    |--------------------------------------------------------------------------
    |
    | Apple Pay payment processing certificate path or content.
    |
    */
    'apple_pay_certificate' => env('KPAY_APPLE_PAY_CERTIFICATE', ''),

    /*
    |--------------------------------------------------------------------------
    | Payment Table Name
    |--------------------------------------------------------------------------
    |
    | Table name to store payment records.
    | 
    | Options:
    | - 'kpay_payments' (default) - Creates new table
    | - 'payments' - Use existing payments table
    | - 'transactions' - Use existing transactions table
    | - Any custom table name
    |
    | If using existing table, make sure it has the required columns:
    | - id, payment_id, track_id, result, result_code, auth, ref, trans_id,
    |   post_date, udf1-udf5, amount, currency, payment_method, status,
    |   response_data, request_data, created_at, updated_at
    |
    */
    'payment_table' => env('KPAY_PAYMENT_TABLE', 'kpay_payments'),

    /*
    |--------------------------------------------------------------------------
    | Create Payment Table
    |--------------------------------------------------------------------------
    |
    | Set to false if you want to use an existing table and skip migration.
    | Set to true to create the payment table via migration.
    |
    */
    'create_payment_table' => env('KPAY_CREATE_TABLE', true),

];

