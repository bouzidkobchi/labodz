<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.patient_portal') }} - {{ __('messages.lab_name') }}</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3182ce;
            --primary-dark: #2c5282;
            --secondary-color: #edf2f7;
            --text-main: #2d3748;
            --text-muted: #718096;
            --glass-bg: rgba(255, 255, 255, 0.95);
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Tajawal', sans-serif" : "'Outfit', sans-serif" }};
            background: linear-gradient(135deg, #f6f9fc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            padding: 20px;
        }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            background: var(--primary-color);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }

        .login-header i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .input-group-text {
            background-color: transparent;
            border-right: none;
            color: var(--text-muted);
        }

        .form-control {
            border-left: none;
            padding: 12px;
            border-radius: 0 12px 12px 0;
            border-color: #e2e8f0;
        }
        
        [dir="rtl"] .form-control {
            border-right: none;
            border-left: 1px solid #e2e8f0;
            border-radius: 12px 0 0 12px;
        }
        
        [dir="rtl"] .input-group-text {
            border-left: none;
            border-right: 1px solid #e2e8f0;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }

        .btn-access {
            background: var(--primary-color);
            color: white;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            border: none;
            transition: all 0.3s;
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-access:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(49, 130, 206, 0.2);
        }

        .help-text {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: var(--text-muted);
        }

        .alert-error {
            background-color: #fff5f5;
            border: 1px solid #feb2b2;
            color: #c53030;
            border-radius: 12px;
            padding: 12px;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-user-shield"></i>
            <h1>{{ __('messages.patient_portal') }}</h1>
            <p class="mb-0 opacity-75">{{ __('messages.portal_welcome_subtitle') }}</p>
        </div>

        <div class="login-body">
            @if($errors->has('access'))
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first('access') }}
                </div>
            @endif

            <form action="{{ route('patient.access') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label">{{ __('messages.phone_number') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="05XXXXXXXX" required value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">{{ __('messages.visit_id') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        <input type="text" name="reservation_id" id="reservation_id" class="form-control" placeholder="Ex: 123" required value="{{ old('reservation_id') }}">
                    </div>
                </div>

                <button type="submit" class="btn-access">
                    {{ __('messages.access_my_results') }}
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="help-text">
                <i class="fas fa-info-circle me-1"></i>
                {{ __('messages.portal_login_help') }}
            </div>
            
            <div class="mt-4 text-center">
                <a href="{{ route('home') }}" class="text-decoration-none text-muted small">
                    <i class="fas fa-home me-1"></i> {{ __('messages.back_to_site') }}
                </a>
            </div>
        </div>
    </div>

</body>
</html>
