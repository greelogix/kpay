<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - KNET Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 2.5rem;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                </svg>
            </div>
            <h1 class="mb-3">Payment Successful!</h1>
            <p class="text-muted mb-4">{{ session('message', 'Your payment has been processed successfully.') }}</p>
            
            @if(session('payment'))
                @php
                    $payment = session('payment');
                @endphp
                <div class="card bg-light mb-4">
                    <div class="card-body text-start">
                        <h6 class="card-title">Payment Details</h6>
                        <hr>
                        @if($payment->track_id)
                            <p class="mb-2"><strong>Order ID:</strong> {{ $payment->track_id }}</p>
                        @endif
                        @if($payment->trans_id)
                            <p class="mb-2"><strong>Transaction ID:</strong> {{ $payment->trans_id }}</p>
                        @endif
                        @if($payment->amount)
                            <p class="mb-2"><strong>Amount:</strong> {{ number_format($payment->amount, 3) }} {{ $payment->currency ?? 'KWD' }}</p>
                        @endif
                        @if($payment->ref)
                            <p class="mb-2"><strong>Reference:</strong> {{ $payment->ref }}</p>
                        @endif
                        @if($payment->auth)
                            <p class="mb-0"><strong>Authorization:</strong> {{ $payment->auth }}</p>
                        @endif
                    </div>
                </div>
            @endif
            
            <a href="{{ url('/') }}" class="btn btn-primary btn-lg">Return to Home</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

