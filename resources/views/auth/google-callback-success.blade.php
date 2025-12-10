<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Login Successful' : 'Login Failed' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 400px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        h1 {
            font-size: 1.5rem;
            margin: 0 0 0.5rem 0;
            color: #1f2937;
        }
        p {
            color: #6b7280;
            margin: 0 0 1.5rem 0;
        }
        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        @if($success)
            <div class="icon success">✓</div>
            <h1>Login Successful!</h1>
            <p>{{ $message }}</p>
        @else
            <div class="icon error">✕</div>
            <h1>Login Failed</h1>
            <p>{{ $message }}</p>
        @endif
        <div class="spinner"></div>
        <p style="margin-top: 1rem; font-size: 0.875rem;">Closing window...</p>
    </div>

    <script>
        // Close the popup and refresh the parent window
        if (window.opener) {
            // Notify parent window about the login status
            try {
                window.opener.postMessage({
                    type: 'google-auth-complete',
                    success: {{ $success ? 'true' : 'false' }},
                    message: '{{ $message }}'
                }, '*');
            } catch (e) {
                console.error('Failed to post message to parent:', e);
            }
            
            // Close popup after a short delay
            setTimeout(function() {
                window.close();
            }, 1000);
        } else {
            // If no opener (shouldn't happen), redirect to home
            setTimeout(function() {
                window.location.href = '/';
            }, 2000);
        }
    </script>
</body>
</html>
