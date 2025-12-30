<?php

namespace Greelogix\KPay\Constants;

/**
 * KNET Payment Gateway Error Codes
 * Based on KNET Integration Manual K-064 v1.5
 */
class KPayErrorCodes
{
    // Standard PG Transaction Error Codes
    public const INVALID_CREDENTIALS = 'Invalid credentials';
    public const INVALID_AMOUNT = 'Invalid amount';
    public const INVALID_CURRENCY = 'Invalid currency';
    public const INVALID_TRACK_ID = 'Invalid track ID';
    public const TRANSACTION_FAILED = 'Transaction failed';
    public const HASH_VALIDATION_FAILED = 'Hash validation failed';
    public const PAYMENT_NOT_FOUND = 'Payment not found';
    
    // Common KNET Error Codes (from documentation)
    public const ERROR_CODES = [
        // Standard errors
        'MPG02001' => 'Invalid merchant credentials',
        'MPG02002' => 'Invalid transaction amount',
        'MPG02003' => 'Invalid currency code',
        'MPG02004' => 'Invalid track ID',
        'MPG02005' => 'Transaction timeout',
        'MPG02006' => 'Duplicate transaction',
        
        // Refund errors
        'MPG03001' => 'Refund amount exceeds original transaction',
        'MPG03002' => 'Original transaction not found',
        'MPG03003' => 'Refund already processed',
        
        // Inquiry errors
        'MPG04001' => 'Transaction not found',
        'MPG04002' => 'Invalid inquiry parameters',
    ];

    /**
     * Get error message by code
     */
    public static function getMessage(string $code): string
    {
        return self::ERROR_CODES[$code] ?? 'Unknown error: ' . $code;
    }

    /**
     * Check if error code exists
     */
    public static function exists(string $code): bool
    {
        return isset(self::ERROR_CODES[$code]);
    }
}

