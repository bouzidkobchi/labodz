<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.patient_portal') }} / {{ __('messages.physician_portal') }} - {{ __('messages.lab_name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3182ce;
            --primary-dark: #2c5282;
            --secondary-color: #48bb78;
            --bg-body: #f8fafc;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Tajawal', sans-serif" : "'Outfit', sans-serif" }};
            background-color: var(--bg-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: white;
            width: 100%;
            max-width: 480px;
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: none;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .lab-logo {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
            color: white;
        }

        .card-tabs {
            display: flex;
            border-bottom: 1px solid #edf2f7;
        }

        .tab-btn {
            flex: 1;
            padding: 20px;
            text-align: center;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            color: #718096;
            border-bottom: 3px solid transparent;
        }

        .tab-btn.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background: #fdfdfd;
        }

        .tab-btn i {
            display: block;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .login-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }

        .btn-access {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-access:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .lang-switch {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        [dir="rtl"] .lang-switch {
            right: auto;
            left: 20px;
        }

        .alert-custom {
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

    <div class="lang-switch">
        <a href="{{ route('lang.switch', app()->getLocale() == 'ar' ? 'fr' : 'ar') }}" class="btn btn-sm btn-light shadow-sm rounded-pill px-3">
            <i class="fas fa-globe me-1"></i> {{ app()->getLocale() == 'ar' ? 'Français' : 'العربية' }}
        </a>
    </div>

    <div class="login-card">
        <div class="login-header">
            <a href="{{ route('home') }}" class="lab-logo">
                <i class="fas fa-microscope me-2"></i> {{ __('messages.lab_name') }}
            </a>
            <p class="mb-0 opacity-75">{{ __('messages.portal_welcome_subtitle') }}</p>
        </div>

        <div class="card-tabs" id="authTabs">
            <div class="tab-btn active" onclick="switchTab('patient')">
                <i class="fas fa-user"></i>
                {{ __('messages.patient_portal') }}
            </div>
            <div class="tab-btn" onclick="switchTab('doctor')">
                <i class="fas fa-user-md"></i>
                {{ __('messages.physician_portal') }}
            </div>
        </div>

        <div class="login-body">
            @if($errors->any())
                <div class="alert alert-danger alert-custom">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Patient Form -->
            <form id="patientForm" action="{{ route('access.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="auth_type" value="patient">
                
                <div class="mb-4">
                    <label class="form-label">{{ __('messages.phone_number') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-phone text-muted small"></i></span>
                        <input type="text" name="phone" class="form-control border-start-0" placeholder="0XXXXXXXXX" value="{{ old('phone') }}" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">{{ __('messages.case_number') ?? 'Case Number' }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-hashtag text-muted small"></i></span>
                        <input type="text" name="reservation_id" class="form-control border-start-0" placeholder="{{ __('messages.case_number_placeholder') ?? 'V-123...' }}" value="{{ old('reservation_id') }}" required>
                    </div>
                </div>

                <button type="submit" class="btn-access">
                    <i class="fas fa-sign-in-alt me-2"></i> {{ __('messages.access_my_results') }}
                </button>
            </form>

            <!-- Doctor Form -->
            <form id="doctorForm" action="{{ route('access.submit') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="auth_type" value="doctor">

                <div class="mb-4">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted small"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" placeholder="doctor@example.com" value="{{ old('email') }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">{{ __('messages.password') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted small"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••">
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="rememberMe">
                    <label class="form-check-label small" for="rememberMe">{{ __('messages.remember_me') ?? 'Souvenir de moi' }}</label>
                </div>

                <button type="submit" class="btn-access" style="background: var(--secondary-color);">
                    <i class="fas fa-user-shield me-2"></i> {{ __('messages.login_as_doctor') }}
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('home') }}" class="text-decoration-none small text-muted">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back_to_home') ?? 'Retour à l\'accueil' }}
                </a>
            </div>
        </div>
    </div>

    <script>
        function switchTab(type) {
            const tabs = document.querySelectorAll('.tab-btn');
            const patientForm = document.getElementById('patientForm');
            const doctorForm = document.getElementById('doctorForm');

            tabs.forEach(t => t.classList.remove('active'));
            
            if (type === 'patient') {
                tabs[0].classList.add('active');
                patientForm.style.display = 'block';
                doctorForm.style.display = 'none';
            } else {
                tabs[1].classList.add('active');
                patientForm.style.display = 'none';
                doctorForm.style.display = 'block';
            }
        }

        // Maintain tab across validation errors
        @if(old('auth_type') == 'doctor' || $errors->has('doctor_auth'))
            switchTab('doctor');
        @endif
    </script>
</body>
</html>
