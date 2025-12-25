<?php

namespace Greelogix\KPay\Events;

use Greelogix\KPay\Models\KPayPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated
{
    use Dispatchable, SerializesModels;

    public KPayPayment $payment;

    /**
     * Create a new event instance.
     */
    public function __construct(KPayPayment $payment)
    {
        $this->payment = $payment;
    }
}


