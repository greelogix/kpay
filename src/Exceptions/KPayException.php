<?php

namespace Greelogix\KPay\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class KPayException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        // Log the exception
        Log::error('KPay Exception', [
            'message' => $message,
            'code' => $code,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ]);
    }
}


