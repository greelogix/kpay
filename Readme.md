# KNET Payment Laravel Package

A lightweight Laravel package for KNET payment gateway integration. Simple payment service - no admin panels, no database settings management.

**✨ Core Payment Service** - Just payment processing, nothing extra.

## Features

- ✅ Initiate payment with KNET
- ✅ Get payment methods (from KNET API or standard list)
- ✅ Payment callback handling (automatic)
- ✅ Success and error pages
- ✅ Payment response validation with hash verification
- ✅ Refund processing support
- ✅ Event system for payment status updates
- ✅ Laravel 10.x, 11.x, and 12.x compatible
- ✅ Auto-discovery enabled
- ✅ Comprehensive error handling
- ✅ Payment status tracking

## Requirements

- PHP >= 8.1
- Laravel 10.x, 11.x, or 12.x
- Composer
- KNET Merchant Account (for production - Tranportal ID, Password, Resource Key from your acquiring bank)

## Quick Start

```bash
# 1. Add repository to composer.json (see Step 1 below)

# 2. Install package
composer require greelogix/kpayment-laravel:dev-main

# 3. Publish assets
php artisan vendor:publish --tag=kpayment

# 4. Run migrations
php artisan migrate

# 5. Configure .env (see Step 5 below)

# 6. Clear cache
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
```

**That's it!** The package is ready to use. No seeders, no admin setup needed.

## Installation

### Step 1: Add Package Repository to composer.json

**IMPORTANT:** You must add the repository to `composer.json` BEFORE running `composer require`.

Open `composer.json` in your Laravel project root and add the repository:

**For SSH (recommended):**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:greelogix/kpayment.git"
        }
    ]
}
```

**For HTTPS:**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/greelogix/kpayment.git"
        }
    ]
}
```

**If using private repository, configure authentication:**

For SSH: Ensure your SSH key is added to GitHub.

For HTTPS with token:
```bash
composer config github-oauth.github.com your_token_here
```

### Step 2: Install Package

```bash
composer require greelogix/kpayment-laravel:dev-main
```

The package will be automatically discovered by Laravel (auto-discovery is enabled).

**Note:** If you get "package not found" error:
- Ensure the repository is added to `composer.json` first
- Save `composer.json` after adding the repository
- For private repos, ensure authentication is configured
- Run `composer update` if needed

### Step 3: Publish Package Assets

```bash
php artisan vendor:publish --tag=kpayment
```

This will publish:
- `config/kpayment.php` → `config/kpayment.php`
- Payment views → `resources/views/vendor/kpayment/`
- Migrations → `database/migrations/`
- Language files → `lang/vendor/kpayment/`

### Step 4: Run Migrations

```bash
php artisan migrate
```

This will create the `kpayment_payments` table for payment tracking.

### Step 5: Configure Settings

Configure via `.env` file:

```env
# KNET Credentials (not required for testing)
KPAYMENT_TRANPORTAL_ID=
KPAYMENT_TRANPORTAL_PASSWORD=
KPAYMENT_RESOURCE_KEY=

# KNET URLs
KPAYMENT_BASE_URL=https://kpaytest.com.kw/kpg/PaymentHTTP.htm
KPAYMENT_RESPONSE_URL=https://yoursite.com/kpayment/response
KPAYMENT_ERROR_URL=https://yoursite.com/payment/error

# Payment Settings
KPAYMENT_TEST_MODE=true
KPAYMENT_CURRENCY=414
KPAYMENT_LANGUAGE=EN
KPAYMENT_KFAST_ENABLED=false
KPAYMENT_APPLE_PAY_ENABLED=false
```

**For Testing:**
- Leave credentials empty (not required)
- Use test URL: `https://kpaytest.com.kw/kpg/PaymentHTTP.htm`
- Set `KPAYMENT_TEST_MODE=true`

**For Production:**
- Configure all credentials (required)
- Use production URL: `https://www.kpay.com.kw/kpg/PaymentHTTP.htm`
- Set `KPAYMENT_TEST_MODE=false`

### Step 6: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 7: Verify Installation

```bash
php artisan route:list | grep kpayment
```

You should see:
- `POST /kpayment/response` - Payment response handler
- `GET /kpayment/response` - Payment response handler (GET)
- `GET /payment/success` - Payment success page
- `GET /payment/error` - Payment error page

## Usage

### Get Payment Methods

```php
use Greelogix\KPayment\Facades\KPayment;

// Get payment methods (tries API first, falls back to standard methods)
$paymentMethods = KPayment::getPaymentMethods('web');

// Or explicitly get from API
$paymentMethods = KPayment::getPaymentMethodsFromApi('web');

// Returns array of payment methods:
// [
//     ['code' => 'KNET', 'name' => 'KNET Card', 'platform' => ['web', 'ios', 'android']],
//     ['code' => 'VISA', 'name' => 'Visa', 'platform' => ['web', 'ios', 'android']],
//     ['code' => 'MASTERCARD', 'name' => 'Mastercard', 'platform' => ['web', 'ios', 'android']],
//     // ... KFAST and Apple Pay if enabled
// ]
```

**Note:** `getPaymentMethods()` attempts to fetch from KNET API first. If the API is unavailable or KNET doesn't provide a payment methods endpoint, it falls back to standard methods (KNET, Visa, Mastercard, plus KFAST/Apple Pay if enabled).

### Initiate Payment

```php
use Greelogix\KPayment\Facades\KPayment;

$paymentData = KPayment::generatePaymentForm([
    'amount' => 100.000,              // Amount with 3 decimal places
    'track_id' => 'ORDER-12345',      // Your order/tracking ID
    'currency' => '414',               // 414 = KWD, 840 = USD, etc.
    'language' => 'EN',                // EN or AR
    'payment_method_code' => 'VISA',  // Optional: Pre-select method
    'udf1' => 'ORDER-12345',          // Optional: Store order ID
    'udf2' => 'USER-123',             // Optional: Store user ID
    // ... udf3, udf4, udf5
]);

// Returns:
// [
//     'form_url' => 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm',
//     'form_data' => [...], // Form fields to submit
//     'payment_id' => 1,
//     'track_id' => 'ORDER-12345'
// ]

// Use the built-in payment form view
return view('kpayment::payment.form', [
    'formUrl' => $paymentData['form_url'],
    'formData' => $paymentData['form_data'],
]);
```

### Payment Callback (Automatic)

The package automatically handles payment callbacks at `/kpayment/response`. When payment completes:

1. Payment is validated and processed
2. `PaymentStatusUpdated` event is fired
3. User is redirected to success/error page

**Listen to payment events to update your order/booking status:**

```php
// app/Providers/EventServiceProvider.php
use Greelogix\KPayment\Events\PaymentStatusUpdated;

protected $listen = [
    PaymentStatusUpdated::class => [
        \App\Listeners\UpdateOrderStatus::class,
    ],
];
```

```php
// app/Listeners/UpdateOrderStatus.php
namespace App\Listeners;

use Greelogix\KPayment\Events\PaymentStatusUpdated;
use App\Models\Order;

class UpdateOrderStatus
{
    public function handle(PaymentStatusUpdated $event)
    {
        $payment = $event->payment;
        $orderId = $payment->udf1; // Your order ID stored in udf1
        
        if ($payment->isSuccessful()) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => 'paid']);
                // Send confirmation email, etc.
            }
        } elseif ($payment->isFailed()) {
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => 'payment_failed']);
            }
        }
    }
}
```

### Get Payment Status

```php
use Greelogix\KPayment\Facades\KPayment;

// Get payment by track ID (your order ID)
$payment = KPayment::getPaymentByTrackId('ORDER-12345');

if ($payment && $payment->isSuccessful()) {
    // Payment successful
}

// Get payment by transaction ID
$payment = KPayment::getPaymentByTransId('TRANS123456');

// Check payment status
if ($payment->isSuccessful()) {
    // Success
} elseif ($payment->isFailed()) {
    // Failed
} elseif ($payment->isPending()) {
    // Pending
}
```

### Process Refund

```php
use Greelogix\KPayment\Facades\KPayment;

try {
    $refundResult = KPayment::processRefund([
        'trans_id' => 'ORIGINAL_TRANSACTION_ID',
        'track_id' => 'REFUND-TRACK-ID',
        'amount' => 50.000,
    ]);

    if (isset($refundResult['result']) && $refundResult['result'] === 'CAPTURED') {
        // Refund successful
    }
} catch (\Greelogix\KPayment\Exceptions\KnetException $e) {
    // Handle error
}
```

## Payment Response Handling

### Response Routes

The package automatically registers response routes:
- `POST /kpayment/response` (route name: `kpayment.response`)
- `GET /kpayment/response` (route name: `kpayment.response.get`)

These routes are **CSRF exempt** and handle payment responses from KNET.

### Success and Error Pages

The package includes built-in success and error pages:
- `GET /payment/success` (route name: `kpayment.success`)
- `GET /payment/error` (route name: `kpayment.error`)

These pages display payment details and are automatically used by the ResponseController.

### Response Parameters

KNET returns the following parameters:
- `paymentid` - Payment ID
- `trackid` - Track ID (your order ID)
- `result` - Result code (CAPTURED, NOT CAPTURED, etc.)
- `auth` - Authorization code
- `ref` - Reference number
- `tranid` - Transaction ID
- `postdate` - Post date
- `udf1` to `udf5` - User defined fields
- `hash` - Response hash for validation

### Response Events

The package fires the following event when payment status is updated:

```php
\Greelogix\KPayment\Events\PaymentStatusUpdated
```

## Configuration

All configuration is done via `.env` file or `config/kpayment.php`:

```php
// config/kpayment.php
return [
    'tranportal_id' => env('KPAYMENT_TRANPORTAL_ID', ''),
    'tranportal_password' => env('KPAYMENT_TRANPORTAL_PASSWORD', ''),
    'resource_key' => env('KPAYMENT_RESOURCE_KEY', ''),
    'base_url' => env('KPAYMENT_BASE_URL', 'https://kpaytest.com.kw/kpg/PaymentHTTP.htm'),
    'test_mode' => env('KPAYMENT_TEST_MODE', true),
    'response_url' => env('KPAYMENT_RESPONSE_URL', ''),
    'error_url' => env('KPAYMENT_ERROR_URL', ''),
    'currency' => env('KPAYMENT_CURRENCY', '414'),
    'language' => env('KPAYMENT_LANGUAGE', 'EN'),
    'kfast_enabled' => env('KPAYMENT_KFAST_ENABLED', false),
    'apple_pay_enabled' => env('KPAYMENT_APPLE_PAY_ENABLED', false),
];
```

## Complete Example

### 1. Create Order in Your System

```php
// In your checkout controller
$order = Order::create([
    'user_id' => auth()->id(),
    'total' => 100.000,
    'status' => 'pending',
    // ... other fields
]);
```

### 2. Initiate Payment

```php
use Greelogix\KPayment\Facades\KPayment;

$paymentData = KPayment::generatePaymentForm([
    'amount' => $order->total,
    'track_id' => (string)$order->id,  // Use order ID as track_id
    'udf1' => (string)$order->id,      // Store order ID for event listener
    'currency' => '414',
    'language' => 'EN',
]);

return view('kpayment::payment.form', [
    'formUrl' => $paymentData['form_url'],
    'formData' => $paymentData['form_data'],
]);
```

### 3. Handle Payment Event

```php
// app/Listeners/UpdateOrderStatus.php
public function handle(PaymentStatusUpdated $event)
{
    $payment = $event->payment;
    $orderId = $payment->udf1; // Your order ID
    
    if ($payment->isSuccessful()) {
        $order = Order::find($orderId);
        if ($order) {
            $order->update(['status' => 'paid', 'paid_at' => now()]);
            // Send email, update inventory, etc.
        }
    }
}
```

**That's it!** The package handles everything else automatically.

## Testing

### Test Mode

**Important:** KNET test environment does NOT require any credentials or API keys for testing.

1. Set `KPAYMENT_TEST_MODE=true` in `.env`
2. **Leave credentials empty** (Tranportal ID, Password, and Resource Key can be empty for testing)
3. Use test base URL: `https://kpaytest.com.kw/kpg/PaymentHTTP.htm`
4. You can test the payment flow without any credentials

### Test Cards

Refer to your acquiring bank for test card numbers. Test cards typically have:
- Expiration date: Future date (e.g., 12/2025)
- CVV: Any 3 digits

## Currency Codes

Common currency codes:
- `414` - Kuwaiti Dinar (KWD)
- `840` - US Dollar (USD)
- `682` - Saudi Riyal (SAR)
- `978` - Euro (EUR)
- `826` - British Pound (GBP)

## Language Codes

- `EN` - English
- `AR` - Arabic

## Production Checklist

- [ ] Set `KPAYMENT_TEST_MODE=false` in `.env`
- [ ] **Configure all credentials** (Tranportal ID, Password, Resource Key)
  - These are **REQUIRED** for production
- [ ] Use production credentials from your acquiring bank
- [ ] Set base URL to `https://www.kpay.com.kw/kpg/PaymentHTTP.htm`
- [ ] Configure response URL (must be publicly accessible)
- [ ] Configure error URL (must be publicly accessible)
- [ ] Test payment flow end-to-end
- [ ] Verify response handling works correctly
- [ ] Monitor payment logs
- [ ] Test refund functionality

## Security

- Response validation uses SHA-256 hash verification
- CSRF protection enabled (response routes are exempt)
- Resource key never exposed in frontend
- All payment data validated before processing

## Troubleshooting

### Routes Not Working

1. Clear route cache: `php artisan route:clear`
2. Verify package is discovered: `php artisan package:discover`
3. Check routes: `php artisan route:list | grep kpayment`

### Payment Response Not Received

1. Check response URL is correctly configured in `.env`
2. Verify response URL is accessible from internet (not localhost)
3. Check Laravel logs: `storage/logs/laravel.log`
4. Ensure CSRF exemption is working for response route

### Hash Validation Failed

1. Verify resource key is correct in `.env`
2. Check that all parameters are included in hash calculation
3. Ensure no parameters are modified before validation

## License

MIT

## Support

For issues and questions:

- Check the [KNET Integration Manual](https://www.knet.com.kw/)
- Review package documentation
- Contact: asad.ali@greelogix.com

## Changelog

### Version 2.0.0

- Simplified package - removed admin panels and settings management
- Payment methods now returned from service (tries API, falls back to standard)
- Configuration via config/env only
- Core payment functionality only
- Added `getPaymentMethodsFromApi()` method

### Version 1.0.0

- Initial release
- Complete KNET Payment Gateway integration
- Payment response handling with validation
- Refund processing support
- KFAST support
- Apple Pay support
- Payment status tracking
