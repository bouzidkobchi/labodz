<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.doctor_login') }} - {{ __('messages.lab_name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --medical-primary: #2c5282;
            --medical-secondary: #4299e1;
            --medical-bg: #f0f4f8;
            --text-dark: #2d3748;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Tajawal', sans-serif" : "'Outfit', sans-serif" }};
            background-color: var(--medical-bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .card-header-medical {
            background: var(--medical-primary);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .header-icon {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ebf8ff;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #cbd5e0;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
            border-color: var(--medical-secondary);
        }

        .btn-medical {
            background: var(--medical-primary);
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            border: none;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-medical:hover {
            background: #2a4365;
            transform: translateY(-1px);
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="card-header-medical">
            <div class="header-icon"><i class="fas fa-user-md"></i></div>
            <h2 class="fw-bold mb-0">{{ __('messages.physician_portal') }}</h2>
            <div class="role-badge">{{ __('messages.medical_professional_access') }}</div>
        </div>

        <div class="login-body">
            @if ($errors->any())
                <div class="alert alert-danger mb-4 rounded-3 border-0">
                    <ul class="mb-0 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('doctor.login.submit') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-600">{{ __('messages.email_address') }}</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}" autofocus>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-600">{{ __('messages.password') }}</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">{{ __('messages.remember_me') }}</label>
                </div>

                <button type="submit" class="btn-medical">
                    {{ __('messages.login_as_doctor') }}
                </button>
            </form>

            <div class="back-link">
                <a href="{{ route('home') }}" class="text-decoration-none text-muted">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back_to_site') }}
                </a>
            </div>
        </div>
    </div>

</body>
</html>
