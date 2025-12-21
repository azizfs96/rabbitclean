<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Failed') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .icon {
            width: 80px;
            height: 80px;
            background: #EF4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }
        h1 {
            color: #1F2937;
            font-size: 24px;
            margin-bottom: 12px;
        }
        p {
            color: #6B7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .error-message {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            color: #DC2626;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 14px 32px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            margin: 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #6B7280;
        }
        .note {
            margin-top: 20px;
            font-size: 14px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>
        <h1>{{ __('Payment Failed') }}</h1>
        <p>{{ __('Unfortunately, we could not process your payment. Please try again.') }}</p>
        
        @if(isset($message))
        <div class="error-message">
            {{ $message }}
        </div>
        @endif
        
        <div>
            <a href="javascript:void(0)" class="btn" onclick="closeWindow()">
                {{ __('Try Again') }}
            </a>
        </div>
        
        <p class="note">{{ __('If the problem persists, please contact support.') }}</p>
    </div>

    <script>
        function closeWindow() {
            window.close();
            setTimeout(function() {
                // You can add your app's deep link here
                // window.location.href = 'rabbitclean://subscription/failed';
                alert('{{ __("Please return to the app and try again.") }}');
            }, 500);
        }
    </script>
</body>
</html>
