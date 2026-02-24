<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.medical_eligibility_report') }} - {{ $reservation->patient->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&family=Inter:wght@400;500;700&display=swap');

        body {
            font-family: {{ app()->getLocale() == 'ar' ? "'Tajawal', sans-serif" : "'Inter', sans-serif" }};
            background: white;
            color: #2d3748;
            padding: 20px;
        }

        .report-header {
            border-bottom: 2px solid #3182ce;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .lab-logo {
            font-size: 24px;
            font-weight: bold;
            color: #3182ce;
        }

        .report-title {
            text-align: center;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2d3748;
            font-weight: 700;
        }

        .info-section {
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8fafc;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item strong {
            color: #4a5568;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
        }

        .status-block { background-color: #fee2e2; color: #991b1b; }
        .status-warning { background-color: #fef3c7; color: #92400e; }
        .status-eligible { background-color: #d1fae5; color: #065f46; }

        .table thead th {
            background-color: #edf2f7;
            color: #4a5568;
            border-bottom: 2px solid #cbd5e0;
        }

        .answer-tag {
            background-color: white;
            border: 1px solid #e2e8f0;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-right: 5px;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #718096;
            text-align: center;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
            .info-section { background-color: transparent !important; }
            .table-hover tbody tr:hover { background-color: transparent !important; }
            @page { margin: 2cm; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
    <div class="no-print mb-4 text-center">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-4 shadow">
            <i class="fas fa-print me-2"></i> {{ __('messages.print_report') }}
        </button>
        <button id="download-btn" class="btn btn-success btn-lg px-4 shadow ms-2">
            <i class="fas fa-file-pdf me-2"></i> {{ __('messages.download_pdf') }}
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-lg px-4 ms-2">
            <i class="fas fa-times me-2"></i> {{ __('messages.close') }}
        </button>
    </div>

    <div id="report-content">
        <!-- Professional Medical Letterhead -->
        <div class="report-header border-bottom border-2 border-dark pb-3 mb-4">
            <div class="row align-items-center">
                <div class="col-6">
                    <h1 class="h3 fw-bold mb-1" style="color: #1a365d;">labo.dz</h1>
                    <div class="small text-muted">
                        <div><i class="fas fa-map-marker-alt me-1"></i> {{ __('messages.address') }}</div>
                        <div><i class="fas fa-phone me-1"></i> {{ __('messages.phone_val') }}</div>
                        <div><i class="fas fa-envelope me-1"></i> {{ __('messages.email_val') }}</div>
                    </div>
                </div>
                <div class="col-6 text-end">
                    @if($barcode)
                        <div class="d-inline-block text-center me-3" style="vertical-align: middle;">
                            <img src="{{ $barcode }}" width="90" alt="Tracking QR">
                            <div style="font-size: 7px; color: #718096; margin-top: 2px;">MEDICAL TRACKING ID</div>
                        </div>
                    @endif
                    <div class="d-inline-block text-start border-start ps-3" style="vertical-align: middle;">
                        <div class="small fw-bold text-uppercase text-muted">{{ __('messages.print_date') }}</div>
                        <div class="fw-bold">{{ now()->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="text-center fw-bold mb-5 text-uppercase" style="color: #1a365d; letter-spacing: 2px; border-bottom: 2px double #e2e8f0; padding-bottom: 10px;">
            {{ __('messages.medical_eligibility_assessment_report') }}
        </h2>

        <div class="info-section mb-4 p-4 border rounded shadow-sm bg-light">
            <h5 class="fw-bold mb-3 text-secondary text-uppercase border-bottom pb-2">
                <i class="fas fa-id-card me-2"></i> {{ __('messages.patient_information') }}
            </h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="small text-muted text-uppercase fw-bold">{{ __('messages.full_name') }}</div>
                    <div class="fw-bold h6">{{ $reservation->patient->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted text-uppercase fw-bold">{{ __('messages.id_number') }}</div>
                    <div class="fw-bold h6">#{{ $reservation->patient->id }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted text-uppercase fw-bold">{{ __('messages.gender') }}</div>
                    <div class="fw-bold h6">{{ $reservation->patient->gender == 'male' ? __('messages.male') : __('messages.female') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted text-uppercase fw-bold">{{ __('messages.phone_number') }}</div>
                    <div class="fw-bold h6">{{ $reservation->patient->phone }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted text-uppercase fw-bold">{{ __('messages.appointment_date') }}</div>
                    <div class="fw-bold h6">{{ $reservation->analysis_date }} {{ $reservation->time }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-muted text-uppercase fw-bold">{{ __('messages.visit_number') }}</div>
                    <div class="fw-bold h6">REF-{{ str_pad($reservation->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <h5 class="fw-bold mb-3 text-secondary text-uppercase"><i class="fas fa-stethoscope me-2"></i> {{ __('messages.analyses_assessment_summary') }}</h5>
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="py-3 px-4">{{ __('messages.analysis') }}</th>
                        <th class="py-3 px-4" style="width: 220px;">{{ __('messages.status') }}</th>
                        <th class="py-3 px-4">{{ __('messages.assessment_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                    <tr>
                        <td class="fw-bold px-4">{{ $result['name'] }}</td>
                        <td class="px-4">
                            <div class="status-badge status-{{ $result['status'] }} w-100 text-center py-2 border">
                                @if($result['status'] === 'block')
                                    <i class="fas fa-times-circle me-1"></i> {{ __('messages.rejected_status') }}
                                @elseif($result['status'] === 'warning')
                                    <i class="fas fa-exclamation-triangle me-1"></i> {{ __('messages.medical_warning') }}
                                @else
                                    <i class="fas fa-check-circle me-1"></i> {{ __('messages.qualified') }}
                                @endif
                            </div>
                        </td>
                        <td class="px-4">
                            @if(count($result['notes']) > 0)
                                <ul class="mb-0 small ps-3">
                                    @foreach($result['notes'] as $note)
                                        <li class="mb-1 fw-500">{{ $note }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-success small fw-bold"><i class="fas fa-check me-1"></i> {{ __('messages.met_all_protocol_requirements') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-5">
            <h5 class="fw-bold mb-3 text-secondary text-uppercase"><i class="fas fa-clipboard-check me-2"></i> {{ __('messages.pre_analysis_patient_answers') }}</h5>
            <table class="table table-striped table-bordered align-middle">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th class="py-3 px-4" style="width: 65%;">{{ __('messages.behavior_or_condition') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('messages.patient_response') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patientAnswers as $questionId => $answers)
                        @php $question = $answers->first()->question; @endphp
                        <tr>
                            <td class="py-3 px-4">
                                <div class="fw-bold text-dark mb-1">{{ $question->question }}</div>
                                @if($question->analyse_id)
                                    <div class="badge bg-info text-dark" style="font-weight: 500;">
                                        <i class="fas fa-flask fa-xs me-1"></i> {{ $question->analyse?->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-center px-4">
                                @foreach($answers as $ans)
                                    <span class="badge bg-white text-dark border p-2 px-3 m-1" style="font-size: 0.95rem; font-weight: 600;">{{ $ans->option->text }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-5 text-muted fst-italic">
                                <i class="fas fa-info-circle me-2"></i> {{ __('messages.no_diagnostic_answers_recorded') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Professional Footer & Signatures -->
        <div class="mt-5 pt-5 pb-4 border-top">
            <div class="row text-center px-5">
                <div class="col-6">
                    <div class="mb-5 fw-bold text-muted text-uppercase small">{{ __('messages.technician_signature') }}</div>
                    <div class="mx-auto" style="border-top: 2px solid #2d3748; width: 180px;"></div>
                </div>
                <div class="col-6">
                    <div class="mb-5 fw-bold text-muted text-uppercase small">{{ __('messages.doctor_stamp') }}</div>
                    <div class="mx-auto" style="border-top: 2px solid #2d3748; width: 180px;"></div>
                </div>
            </div>
            <div class="mt-5 text-center small text-muted fst-italic">
                <p>labo.dz - {{ __('messages.automated_medical_assessment_report') }}</p>
                <p style="font-size: 0.7rem;">Document généré pour usage interne et validation médicale. Toute modification non autorisée annule la validité du document.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('download-btn').addEventListener('click', function() {
            const element = document.getElementById('report-content');
            const patientName = "{{ $reservation->patient->name }}".replace(/\s+/g, '_');
            const date = "{{ now()->format('Y-m-d') }}";
            
            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     `Report_${patientName}_${date}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, letterRendering: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // New Promise-based usage:
            html2pdf().set(opt).from(element).save();
        });
    </script>
</body>
</html>
