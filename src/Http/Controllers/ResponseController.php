<?php

namespace Greelogix\KPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Greelogix\KPay\Facades\KPay;
use Greelogix\KPay\Events\PaymentStatusUpdated;
use Greelogix\KPay\Exceptions\KPayException;

class ResponseController extends Controller
{
    /**
     * Handle payment response from KNET
     * According to KNET documentation, responses can come via POST or GET
     * Both methods are supported
     */
    public function handle(Request $request)
    {
        try {
            // Get all request parameters (works for both GET and POST)
            $response = $request->all();
            
            // Handle KNET validation/ping requests (empty or test requests)
            // KNET may send GET/POST requests to validate the responseURL is accessible
            // Check if this is a validation request (no payment parameters)
            $hasPaymentParams = isset($response['trackid']) || isset($response['trackId']) || 
                               isset($response['paymentid']) || isset($response['PaymentID']) ||
                               isset($response['result']) || isset($response['Result']) ||
                               isset($response['hash']) || isset($response['Hash']);
            
            // If no payment parameters, treat as validation request
            if (!$hasPaymentParams) {
                // Return 200 OK to indicate the endpoint is accessible
                // This allows KNET to validate the responseURL before processing payment
                Log::info('KNET Response URL Validation Request', [
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'params' => array_keys($response),
                ]);
                
                // Return plain text or JSON response (KNET just needs to see 200 OK)
                return response('OK', 200)
                    ->header('Content-Type', 'text/plain');
            }
            
            // Log incoming response for debugging (mandatory per KNET best practices)
            Log::info('KNET Payment Response Received', [
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'response' => $response,
                'timestamp' => now()->toIso8601String(),
            ]);
            
            // Process the response
            $payment = KPay::processResponse($response);
            
            // Fire event for external integration
            event(new PaymentStatusUpdated($payment));
            
            // Redirect based on payment status
            if ($payment && $payment->isSuccessful()) {
                return $this->handleSuccess($payment, $response);
            } else {
                return $this->handleFailure($payment, $response);
            }
        } catch (KPayException $e) {
            Log::error('KNET Response Error', [
                'method' => $request->method(),
                'ip' => $request->ip(),
                'response' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->handleError($e);
        } catch (\Exception $e) {
            Log::error('KNET Response Unexpected Error', [
                'method' => $request->method(),
                'ip' => $request->ip(),
                'response' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return $this->handleError(new KPayException(__('kpay.response.unexpected_error')));
        }
    }

    /**
     * Handle successful payment
     */
    protected function handleSuccess($payment, array $response)
    {
        // You can customize this redirect URL
        // Priority: udf1 field > route > default URL
        if (!empty($payment->udf1)) {
            $successUrl = $payment->udf1;
        } elseif (Route::has('kpay.success')) {
            $successUrl = route('kpay.success');
        } else {
            $successUrl = url('/payment/success');
        }
        
        return redirect($successUrl)->with([
            'payment' => $payment,
            'message' => __('kpay.response.success'),
        ]);
    }

    /**
     * Handle failed payment
     */
    protected function handleFailure($payment, array $response)
    {
        // You can customize this redirect URL
        // Priority: udf2 field > route > default URL
        if (!empty($payment->udf2)) {
            $errorUrl = $payment->udf2;
        } elseif (Route::has('kpay.error')) {
            $errorUrl = route('kpay.error');
        } else {
            $errorUrl = url('/payment/error');
        }
        
        return redirect($errorUrl)->with([
            'payment' => $payment,
            'message' => __('kpay.response.failed'),
        ]);
    }

    /**
     * Handle error
     */
    protected function handleError($exception)
    {
        // Priority: route > default URL
        if (Route::has('kpay.error')) {
            $errorUrl = route('kpay.error');
        } else {
            $errorUrl = url('/payment/error');
        }
        
        $errorMessage = $exception instanceof KPayException 
            ? $exception->getMessage() 
            : __('kpay.response.error');
        
        return redirect($errorUrl)->with([
            'error' => $errorMessage,
        ]);
    }
}


