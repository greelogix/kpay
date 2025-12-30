<?php

namespace Greelogix\KPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Greelogix\KPay\Models\KPayPayment;
use Greelogix\KPay\Facades\KPay;
use Greelogix\KPay\Exceptions\KPayException;

class RedirectController extends Controller
{

    /**
     * Handle payment redirect - auto-submits form to KNET
     */
    public function redirect(int $paymentId)
    {
        try {
            $payment = KPayPayment::find($paymentId);
            
            if (!$payment) {
                Log::error('KPay Redirect: Payment not found', ['payment_id' => $paymentId]);
                return $this->renderError('Payment not found. Please initiate payment again.', 404);
            }

            $formData = KPay::getPaymentFormData($payment);
            $baseUrl = KPay::getBaseUrl();
            
            Log::info('KPay Redirect: Rendering payment form', [
                'payment_id' => $paymentId,
                'track_id' => $payment->track_id,
                'base_url' => $baseUrl,
            ]);
            
            return view('kpay::payment.form', [
                'formUrl' => $baseUrl,
                'formData' => $formData,
            ]);
        } catch (KPayException $e) {
            Log::error('KPay Redirect: KPayException', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return $this->renderError($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('KPay Redirect: Unexpected error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->renderError('An error occurred while processing your payment request.', 500);
        }
    }

    /**
     * Render error page
     */
    protected function renderError(string $message, int $statusCode = 400)
    {
        return response()->view('kpay::payment.error', [
            'error' => $message,
        ], $statusCode);
    }
}

