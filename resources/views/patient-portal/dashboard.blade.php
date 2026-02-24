<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.patient_dashboard') }} - {{ __('messages.lab_name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3182ce;
            --primary-light: #ebf8ff;
            --success-color: #48bb78;
            --warning-color: #ed8936;
            --danger-color: #e53e3e;
            --text-main: #2d3748;
            --text-muted: #718096;
            --bg-body: #f7fafc;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Tajawal', sans-serif" : "'Outfit', sans-serif" }};
            background-color: var(--bg-body);
            color: var(--text-main);
            padding-bottom: 50px;
        }

        .navbar-patient {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .portal-logo {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary-color);
            text-decoration: none;
        }

        .card-custom {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02), 0 10px 15px rgba(0,0,0,0.03);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .card-custom:hover {
            transform: translateY(-5px);
        }

        .card-header-custom {
            padding: 20px 25px;
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-pill {
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-booked { background: #edf2f7; color: #4a5568; }
        .status-ready { background: #c6f6d5; color: #22543d; }
        .status-completed { background: #bee3f8; color: #2c5282; }
        .status-blocked { background: #fed7d7; color: #822727; }
        .status-warning { background: #feebc8; color: #744210; }

        .analysis-item {
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .analysis-item:last-child { border-bottom: none; }

        .prep-instructions {
            background: #fffaf0;
            border-left: 4px solid #ed8936;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            margin-top: 10px;
        }
        
        [dir="rtl"] .prep-instructions {
            border-left: none;
            border-right: 4px solid #ed8936;
        }

        .btn-download {
            background: var(--primary-color);
            color: white;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-download:hover {
            background: var(--primary-dark);
            color: white;
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.3);
        }

        .info-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .info-value {
            font-weight: 700;
            font-size: 16px;
        }

        .alert-item {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 5px solid;
            display: flex;
            gap: 15px;
        }
        
        [dir="rtl"] .alert-item {
            border-left: none;
            border-right: 5px solid;
        }

        .alert-info { background: #ebf8ff; border-color: #3182ce; color: #2b6cb0; }
        .alert-success { background: #f0fff4; border-color: #38a169; color: #276749; }

        .logout-link {
            padding: 8px 20px;
            border-radius: 10px;
            color: var(--danger-color);
            background: #fff5f5;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .logout-link:hover {
            background: var(--danger-color);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
    </style>
</head>
<body>

    <nav class="navbar-patient">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="#" class="portal-logo">
                <i class="fas fa-microscope me-2"></i> {{ __('messages.lab_name') }}
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-md-inline text-muted small">{{ __('messages.connected_as') }}: <strong>{{ $reservation->patient->name }}</strong></span>
                <form action="{{ route('patient.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-link border-0">
                        <i class="fas fa-sign-out-alt"></i> {{ __('messages.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold mb-1">{{ __('messages.welcome_patient', ['name' => $reservation->patient->name]) }}</h2>
                <p class="text-muted">{{ __('messages.portal_dashboard_desc') }}</p>
            </div>
        </div>

        <div class="row">
            <!-- Left Side: Reservation Summary & Results -->
            <div class="col-lg-8">
                
                <!-- Status & Visit Info -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-calendar-check text-primary"></i>
                        {{ __('messages.visit_details') }}
                    </div>
                    <div class="p-4">
                        <div class="row g-4">
                            <div class="col-md-3 col-6">
                                <div class="info-label">{{ __('messages.visit_id') }}</div>
                                <div class="info-value">#{{ $reservation->id }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-label">{{ __('messages.date') }}</div>
                                <div class="info-value">{{ $reservation->analysis_date }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-label">{{ __('messages.time') }}</div>
                                <div class="info-value">{{ $reservation->time }}</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="info-label">{{ __('messages.status') }}</div>
                                <div>
                                    <span class="status-pill status-{{ $reservation->status }}">
                                        {{ __('messages.' . $reservation->status) }}
                                    </span>
                                </div>
                            </div>
                            @if($reservation->doctor)
                            <div class="col-md-3 col-6">
                                <div class="info-label">{{ __('messages.referred_by') }}</div>
                                <div class="info-value text-primary">
                                    <i class="fas fa-user-md me-1"></i>
                                    {{ $reservation->doctor->name }}
                                </div>
                                <div class="text-muted small">{{ $reservation->doctor->specialty }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-file-medical text-success"></i>
                        {{ __('messages.medical_results') }}
                    </div>
                    <div class="p-4">
                        @if($reservation->result_file_path)
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4 bg-light p-4 rounded-4">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ __('messages.results_ready_title') }}</h5>
                                    <p class="text-muted mb-0 small">{{ __('messages.results_ready_desc') }}</p>
                                </div>
                                <a href="{{ route('patient.download', $reservation->id) }}" class="btn-download">
                                    <i class="fas fa-cloud-download-alt"></i>
                                    {{ __('messages.download_pdf') }}
                                </a>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-hourglass-half"></i>
                                <h5>{{ __('messages.results_pending_title') }}</h5>
                                <p>{{ __('messages.results_pending_desc') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Preparation Section -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-clipboard-list text-warning"></i>
                        {{ __('messages.prep_and_instructions') }}
                    </div>
                    <div>
                        @foreach($reservation->reservationAnalyses as $resAnalysis)
                            <div class="analysis-item">
                                <div>
                                    <div class="fw-bold">{{ $resAnalysis->analyse->name }}</div>
                                    @if($resAnalysis->analyse->preparation_instructions)
                                        <div class="prep-instructions">
                                            <i class="fas fa-info-circle me-1"></i>
                                            {{ $resAnalysis->analyse->preparation_instructions }}
                                        </div>
                                    @else
                                        <div class="text-muted small mt-1">{{ __('messages.no_specific_instructions') }}</div>
                                    @endif
                                </div>
                                <span class="badge bg-light text-dark">{{ $resAnalysis->analyse->category ?? __('messages.analysis') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Right Side: Reports & Alerts -->
            <div class="col-lg-4">
                
                <!-- Detailed Reports -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-print text-primary"></i>
                        {{ __('messages.official_reports') }}
                    </div>
                    <div class="p-4 d-grid gap-2">
                        <a href="{{ route('reservation.pdf', $reservation->id) }}" class="btn btn-outline-secondary w-100 rounded-3 text-start">
                            <i class="fas fa-file-pdf me-2 text-danger"></i> {{ __('messages.appointment_confirmation') }}
                        </a>
                        
                        @php
                            $diagIsChecked = $reservation->reservationAnalyses->pluck('status')->contains(fn($s) => in_array($s, ['ready', 'blocked', 'warning']));
                        @endphp

                        @if($diagIsChecked)
                            <a href="{{ route('admin.bookings.eligibility.print', $reservation->id) }}" target="_blank" class="btn btn-outline-secondary w-100 rounded-3 text-start">
                                <i class="fas fa-notes-medical me-2 text-primary"></i> {{ __('messages.eligibility_report') }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Smart Alerts -->
                <div class="card-custom">
                    <div class="card-header-custom">
                        <i class="fas fa-bell text-warning"></i>
                        {{ __('messages.notifications_alerts') }}
                    </div>
                    <div class="p-4">
                        @if($reservation->reminders->count() > 0)
                            @foreach($reservation->reminders as $reminder)
                                <div class="alert-item alert-info">
                                    <i class="fas fa-info-circle mt-1"></i>
                                    <div>
                                        <div class="fw-bold small">{{ $reminder->sent_at }}</div>
                                        <div class="small">{{ $reminder->message }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state py-2">
                                <p class="small mb-0">{{ __('messages.no_recent_notifications') }}</p>
                            </div>
                        @endif
                        
                        <!-- Fixed Smart Instruction (Example) -->
                        <div class="alert-item alert-success">
                            <i class="fas fa-check-double mt-1"></i>
                            <div>
                                <div class="fw-bold small">{{ __('messages.smart_tip') }}</div>
                                <div class="small">{{ __('messages.drink_water_tip') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
