@extends('Adminstration.layout')

@section('title', __('messages.medical_results'))

@section('content')
<div class="section-header mb-4">
    <h2><i class="fas fa-file-medical"></i> {{ __('messages.medical_results') }}</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('reservations') }}">{{ __('messages.manage_reservations') }}</a></li>
            <li class="breadcrumb-item active">{{ __('messages.medical_results') }}</li>
        </ol>
    </nav>
</div>

<div class="container-fluid px-0">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary">
                <i class="fas fa-user-circle me-2"></i> {{ $reservation->patient->name }} 
                <small class="text-muted ms-2">({{ $reservation->analysis_date }} {{ $reservation->time }})</small>
            </h5>
        </div>
        <div class="card-body p-0">
            <form action="{{ route('admin.bookings.results.update', $reservation->id) }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">{{ __('messages.analyses') }}</th>
                                <th>{{ __('messages.result_value') }}</th>
                                <th>{{ __('messages.unit') }}</th>
                                <th>{{ __('messages.normal_range') }}</th>
                                <th class="pe-4 text-center">{{ __('messages.clinical_status') ?? 'Safety' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservation->reservationAnalyses as $resAnalysis)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $resAnalysis->analyse->name }}</div>
                                    <small class="text-muted">{{ $resAnalysis->analyse->code }}</small>
                                </td>
                                <td>
                                    <input type="text" 
                                           name="results[{{ $resAnalysis->id }}][value]" 
                                           value="{{ $resAnalysis->result_value }}" 
                                           class="form-control" 
                                           placeholder="{{ __('messages.result_value') }}">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="results[{{ $resAnalysis->id }}][unit]" 
                                           value="{{ $resAnalysis->unit }}" 
                                           class="form-control" 
                                           placeholder="{{ __('messages.unit') }}">
                                </td>
                                <td>
                                    <textarea name="results[{{ $resAnalysis->id }}][reference_range]" 
                                              class="form-control" 
                                              rows="2" 
                                              placeholder="{{ __('messages.normal_range') }}">{{ $resAnalysis->reference_range }}</textarea>
                                </td>
                                <td class="pe-4 text-center">
                                    @php 
                                        $statusClass = $resAnalysis->clinical_status == 'CRITICAL' ? 'bg-danger' : 
                                                      ($resAnalysis->clinical_status == 'Normal' ? 'bg-success' : 'bg-secondary');
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill px-3 py-2">
                                        {{ $resAnalysis->clinical_status ?? '---' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center">
                    <div class="form-check form-switch ms-4">
                        <input class="form-check-input" type="checkbox" name="notify_patient" id="notifyPatient" value="1" checked>
                        <label class="form-check-label fw-bold text-primary" for="notifyPatient">
                            <i class="fas fa-paper-plane me-1"></i> {{ __('messages.notify_patient_immediately') ?? 'Notify Patient Immediately' }}
                        </label>
                    </div>
                    <div>
                        <a href="{{ route('reservations') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times"></i> {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {{ __('messages.save_results') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        color: #5a5c69;
    }
</style>
@endsection
