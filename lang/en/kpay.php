<?php

return [
    // Payment Pages
    'payment' => [
        'success' => [
            'title' => 'Payment Success - KNET Payment',
            'heading' => 'Payment Successful!',
            'message' => 'Your payment has been processed successfully.',
            'details' => 'Payment Details',
            'order_id' => 'Order ID',
            'transaction_id' => 'Transaction ID',
            'amount' => 'Amount',
            'reference' => 'Reference',
            'authorization' => 'Authorization',
            'return_home' => 'Return to Home',
        ],
        'error' => [
            'title' => 'Payment Error - KNET Payment',
            'heading' => 'Payment Failed',
            'default_message' => 'Your payment could not be processed. Please try again.',
            'details' => 'Payment Details',
            'order_id' => 'Order ID',
            'transaction_id' => 'Transaction ID',
            'result' => 'Result',
            'amount' => 'Amount',
            'return_home' => 'Return to Home',
            'try_again' => 'Try Again',
        ],
        'form' => [
            'title' => 'Redirecting to KNET Payment Gateway...',
            'redirecting' => 'Redirecting to KNET Payment Gateway...',
            'please_wait' => 'Please wait while we process your payment',
        ],
    ],

    // Response Messages
    'response' => [
        'success' => 'Payment completed successfully',
        'failed' => 'Payment failed',
        'error' => 'An error occurred while processing your payment. Please try again.',
        'invalid_hash' => 'Invalid payment response hash',
        'track_id_not_found' => 'Track ID not found in response',
        'payment_not_found' => 'Payment not found',
        'unexpected_error' => 'An unexpected error occurred while processing payment response.',
    ],

    // Common
    'common' => [
        'currency' => [
            'kwd' => 'KWD',
        ],
    ],
];

