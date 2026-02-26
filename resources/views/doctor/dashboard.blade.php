<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('messages.physician_dashboard') }} - {{ __('messages.lab_name') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --doctor-primary: #1a365d;
            --doctor-secondary: #2b6cb0;
            --doctor-accent: #bee3f8;
            --bg-light: #f7fafc;
            --sidebar-width: 280px;
        }

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Tajawal', sans-serif" : "'Outfit', sans-serif" }};
            background-color: var(--bg-light);
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--doctor-primary);
            min-height: 100vh;
            color: white;
            padding: 30px 20px;
            position: fixed;
            left: 0;
            top: 0;
        }
        
        [dir="rtl"] .sidebar {
            left: auto;
            right: 0;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 40px;
        }
        
        [dir="rtl"] .main-content {
            margin-left: 0;
            margin-right: var(--sidebar-width);
        }

        .brand-logo {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-link-medical {
            color: rgba(255,255,255,0.7);
            padding: 12px 15px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 5px;
            transition: 0.3s;
        }

        .nav-link-medical:hover, .nav-link-medical.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: 0.3s;
        }

        .stat-card:hover { transform: translateY(-5px); }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .icon-patients { background: #ebf8ff; color: #3182ce; }
        .icon-pending { background: #fffaf0; color: #dd6b20; }
        .icon-ready { background: #f0fff4; color: #38a169; }

        /* Data Table */
        .card-table {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            overflow: hidden;
        }

        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #edf2f7;
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #718096;
            padding: 15px 25px;
        }

        .table tbody td {
            padding: 15px 25px;
            border-bottom: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-ready { background: #c6f6d5; color: #22543d; }
        .status-pending { background: #feebc8; color: #744210; }

        .btn-view-result {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            background: #f1f5f9;
            color: #475569;
            transition: 0.3s;
        }

        .btn-view-result:hover {
            background: var(--doctor-secondary);
            color: white;
        }

        .logout-btn {
            margin-top: auto;
            color: #fc8181;
            background: rgba(254, 129, 129, 0.1);
        }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column">
        <div class="brand-logo">
            <i class="fas fa-stethoscope"></i>
            <span>{{ __('messages.lab_name') }}</span>
        </div>

        <nav class="flex-grow-1">
            <a href="#" class="nav-link-medical active">
                <i class="fas fa-th-large"></i> {{ __('messages.dashboard') }}
            </a>
            <a href="#" class="nav-link-medical">
                <i class="fas fa-users"></i> {{ __('messages.referred_patients') }}
            </a>
            <a href="#" class="nav-link-medical">
                <i class="fas fa-cog"></i> {{ __('messages.settings') }}
            </a>
        </nav>

        <div class="user-info mt-auto pt-4 border-top border-secondary">
            <div class="small opacity-75 mb-1">{{ __('messages.connected_as') }}</div>
            <div class="fw-bold mb-3">{{ $doctor->name }}</div>
            
            <form action="{{ route('doctor.logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link-medical logout-btn w-100 border-0">
                    <i class="fas fa-sign-out-alt"></i> {{ __('messages.logout') }}
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="fw-bold h3 mb-1">{{ __('messages.welcome_doctor', ['name' => $doctor->name]) }}</h1>
                <p class="text-muted mb-0">{{ __('messages.doctor_dashboard_subtitle') }}</p>
            </div>
            <div class="date-display text-muted small">
                <i class="far fa-calendar-alt me-2"></i> {{ date('D, d M Y') }}
            </div>
        </header>

        <!-- Stats Overview -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-patients"><i class="fas fa-user-injured"></i></div>
                    <div class="text-muted small fw-bold">{{ __('messages.referred_patients') }}</div>
                    <div class="h2 fw-bold mb-0">{{ $stats['total_patients'] }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-pending"><i class="fas fa-hourglass-start"></i></div>
                    <div class="text-muted small fw-bold">{{ __('messages.pending_results') }}</div>
                    <div class="h2 fw-bold mb-0">{{ $stats['pending_results'] }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-ready"><i class="fas fa-check-circle"></i></div>
                    <div class="text-muted small fw-bold">{{ __('messages.ready_results') }}</div>
                    <div class="h2 fw-bold mb-0">{{ $stats['ready_results'] }}</div>
                </div>
            </div>
        </div>

        <!-- Recent Referrals Table -->
        <div class="card-table">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">{{ __('messages.recent_referred_activities') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('messages.patient_name') }}</th>
                            <th>{{ __('messages.analyses') }}</th>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $res)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $res->patient->name }}</div>
                                    <div class="text-muted small">{{ $res->patient->phone }}</div>
                                </td>
                                <td>
                                    <div class="small text-truncate" style="max-width: 250px;">
                                        {{ $res->reservationAnalyses->map(fn($ra) => $ra->analyse->name)->implode(', ') }}
                                    </div>
                                </td>
                                <td>{{ $res->analysis_date }}</td>
                                <td>
                                    <span class="badge-status status-{{ in_array($res->status, ['ready', 'completed']) ? 'ready' : 'pending' }}">
                                        {{ __('messages.' . $res->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        {{-- 1. Eligibility Report (Always available for doctor's referrals) --}}
                                        <a href="{{ route('reservation.pdf', ['id' => $res->id, 'type' => 'confirmed', 'report_type' => 'eligibility']) }}" class="btn-view-result" title="{{ __('messages.eligibility_report') }}">
                                            <i class="fas fa-file-medical me-1"></i> {{ __('messages.eligibility_report') }}
                                        </a>

                                        {{-- 2. Medical Results (Check DB results or uploaded file) --}}
                                        @php
                                            $hasDbResults = $res->reservationAnalyses->contains(fn($ra) => !empty($ra->result_value));
                                        @endphp

                                        @if($res->result_file_path)
                                            <a href="{{ route('doctor.download', $res->id) }}" class="btn-view-result" style="background: var(--doctor-secondary); color: white;">
                                                <i class="fas fa-file-pdf me-1"></i> {{ __('messages.view_result') }}
                                            </a>
                                        @elseif($hasDbResults)
                                            <a href="{{ route('reservation.pdf', ['id' => $res->id, 'type' => 'confirmed', 'report_type' => 'results']) }}" class="btn-view-result" style="background: #2f855a; color: white;">
                                                <i class="fas fa-microscope me-1"></i> {{ __('messages.medical_report_pdf') }}
                                            </a>
                                        @else
                                            <span class="text-muted small align-self-center py-1"><i class="fas fa-clock me-1"></i> {{ __('messages.waiting') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-folder-open f-large opacity-25 mb-3 d-block"></i>
                                    <p class="text-muted">{{ __('messages.no_referred_patients_found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reservations->hasPages())
                <div class="p-4 bg-light">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </div>

</body>
</html>
