<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.view_result') }} - {{ __('messages.lab_name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --doctor-primary: #1a365d;
            --bg-light: #f7fafc;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Tajawal', sans-serif" : "'Outfit', sans-serif" }};
            background-color: var(--bg-light);
            padding: 40px 0;
        }

        .result-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card-medical {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header-medical {
            background: var(--doctor-primary);
            color: white;
            padding: 25px 35px;
        }

        .analysis-status-badge {
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-ready { background: #c6f6d5; color: #22543d; }
        .status-pending { background: #feebc8; color: #744210; }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            padding: 30px 35px;
            background: #f8fafc;
            border-bottom: 1px solid #edf2f7;
        }

        .info-label {
            font-size: 13px;
            color: #718096;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .info-value {
            font-weight: 700;
            color: #2d3748;
            font-size: 16px;
        }

        .analysis-list {
            padding: 0;
            margin: 0;
        }

        .analysis-row {
            padding: 20px 35px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .analysis-row:last-child { border-bottom: none; }

        .btn-download-result {
            background: var(--doctor-primary);
            color: white;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .btn-download-result:hover {
            opacity: 0.9;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="result-container container">
        
        <div class="mb-4">
            <a href="{{ route('doctor.dashboard') }}" class="text-decoration-none text-muted">
                <i class="fas fa-chevron-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }} me-2"></i> {{ __('messages.back_to_dashboard') }}
            </a>
        </div>

        <div class="card-medical">
            <div class="card-header-medical d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0"><i class="fas fa-file-medical me-2"></i> {{ __('messages.patient_report') }}</h4>
                <div class="analysis-status-badge status-{{ in_array($reservation->status, ['ready', 'completed']) ? 'ready' : 'pending' }}">
                    {{ __('messages.' . $reservation->status) }}
                </div>
            </div>

            <div class="info-grid">
                <div>
                    <div class="info-label">{{ __('messages.patient_name') }}</div>
                    <div class="info-value">{{ $reservation->patient->name }}</div>
                </div>
                <div>
                    <div class="info-label">{{ __('messages.date') }}</div>
                    <div class="info-value">{{ $reservation->analysis_date }}</div>
                </div>
                <div>
                    <div class="info-label">{{ __('messages.visit_id') }}</div>
                    <div class="info-value">#{{ $reservation->id }}</div>
                </div>
                <div>
                    <div class="info-label">{{ __('messages.gender') }}</div>
                    <div class="info-value">{{ __('messages.' . $reservation->patient->gender) }}</div>
                </div>
            </div>

            <div class="p-4 px-5">
                <h5 class="fw-bold mb-4">{{ __('messages.analyses_performed') }}</h5>
                <div class="analysis-list">
                    @foreach($reservation->reservationAnalyses as $ra)
                        <div class="analysis-row">
                            <div>
                                <div class="fw-bold">{{ $ra->analyse->name }}</div>
                                <div class="text-muted small">{{ $ra->analyse->code }}</div>
                            </div>
                            <span class="badge bg-light text-dark p-2 px-3 border rounded-3">{{ __('messages.' . $ra->status) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-5 bg-light border-top d-flex justify-content-center">
                @if($reservation->result_file_path)
                    <a href="{{ route('patient.download', $reservation->id) }}" class="btn-download-result">
                        <i class="fas fa-file-pdf"></i>
                        {{ __('messages.download_official_result_pdf') }}
                    </a>
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-hourglass-half mb-2 d-block h3 opacity-50"></i>
                        <p class="mb-0">{{ __('messages.results_not_yet_validated') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</body>
</html>
