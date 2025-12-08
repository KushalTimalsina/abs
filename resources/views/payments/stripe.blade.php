<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment - {{ $organization->name }}</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .payment-details {
            background: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
            font-weight: 600;
            font-size: 18px;
        }
        .detail-label {
            color: #718096;
        }
        .detail-value {
            color: #2d3748;
            font-weight: 500;
        }
        #card-element {
            background: white;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        #card-errors {
            color: #e53e3e;
            font-size: 14px;
            margin-top: 10px;
            min-height: 20px;
        }
        .submit-button {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .submit-button:hover:not(:disabled) {
            transform: translateY(-2px);
        }
        .submit-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .spinner {
            display: none;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading .spinner {
            display: block;
        }
        .loading .button-text {
            display: none;
        }
        .secure-badge {
            text-align: center;
            margin-top: 20px;
            color: #718096;
            font-size: 12px;
        }
        .secure-badge svg {
            width: 16px;
            height: 16px;
            vertical-align: middle;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $organization->name }}</h1>
            <p>Complete your payment securely with Stripe</p>
        </div>

        <div class="payment-details">
            <div class="detail-row">
                <span class="detail-label">Service:</span>
                <span class="detail-value">{{ $booking->service->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date & Time:</span>
                <span class="detail-value">{{ $booking->slot->start_time->format('M d, Y h:i A') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Duration:</span>
                <span class="detail-value">{{ $booking->service->duration }} minutes</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span class="detail-value">NPR {{ number_format($amount, 2) }}</span>
            </div>
        </div>

        <form id="payment-form">
            @csrf
            <div id="card-element"></div>
            <div id="card-errors"></div>
            
            <button type="submit" class="submit-button" id="submit-button">
                <span class="button-text">Pay NPR {{ number_format($amount, 2) }}</span>
                <div class="spinner"></div>
            </button>
        </form>

        <div class="secure-badge">
            <svg fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            Secured by Stripe
        </div>
    </div>

    <script>
        const stripe = Stripe('{{ $stripePublicKey }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#2d3748',
                    '::placeholder': {
                        color: '#a0aec0',
                    },
                },
            },
        });
        cardElement.mount('#card-element');

        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            submitButton.disabled = true;
            submitButton.classList.add('loading');

            const {paymentMethod, error} = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name: '{{ $booking->customer_name }}',
                    email: '{{ $booking->customer_email }}',
                },
            });

            if (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
                submitButton.disabled = false;
                submitButton.classList.remove('loading');
            } else {
                // Send payment method to server
                fetch('{{ $processUrl }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        payment_method_id: paymentMethod.id,
                        booking_id: '{{ $booking->id }}'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_url;
                    } else {
                        throw new Error(data.message || 'Payment failed');
                    }
                })
                .catch(error => {
                    const errorElement = document.getElementById('card-errors');
                    errorElement.textContent = error.message;
                    submitButton.disabled = false;
                    submitButton.classList.remove('loading');
                });
            }
        });
    </script>
</body>
</html>
