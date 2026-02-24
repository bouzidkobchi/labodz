@extends('Adminstration.layout')

@section('title', __('messages.manage_reservations'))

@section('content')
<div class="section-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-calendar-check"></i> {{ __('messages.manage_reservations') }}</h2>
    <div id="toast-container" class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 9999;"></div>
</div>

<!-- Filters -->
<div class="filters-container">
    <h3><i class="fas fa-filter"></i> {{ __('messages.filter_reservations') }}</h3>
    <form method="GET" action="{{ route('filter.reservations') }}">
        <div class="filter-row">
            <div class="form-group">
                <label>{{ __('messages.start_date') }}</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>
            <div class="form-group">
                <label>{{ __('messages.end_date') }}</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>
            <div class="form-group">
                <label>{{ __('messages.status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('messages.confirmed') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                </select>
            </div>
            <div class="form-group">
                <label>{{ __('messages.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('messages.search') }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> {{ __('messages.apply_filter') }}
        </button>
        <a href="{{ route('reservations') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> {{ __('messages.cancel') }}
        </a>
    </form>
</div>

<!-- Letter Filter -->
<div class="letter-filter mb-4">
    <div class="d-flex flex-wrap gap-1 justify-content-center">
        <a href="{{ request()->fullUrlWithQuery(['letter' => null]) }}" class="btn btn-sm {{ !request('letter') ? 'btn-primary' : 'btn-outline-secondary' }}">ALL</a>
        @foreach(range('A', 'Z') as $char)
            <a href="{{ request()->fullUrlWithQuery(['letter' => $char]) }}" 
               class="btn btn-sm {{ request('letter') == $char ? 'btn-primary' : 'btn-outline-secondary' }}"
               style="width: 35px;">{{ $char }}</a>
        @endforeach
        <a href="{{ request()->fullUrlWithQuery(['letter' => '#']) }}" class="btn btn-sm {{ request('letter') == '#' ? 'btn-primary' : 'btn-outline-secondary' }}">#</a>
    </div>
</div>

<!-- Results Table -->
<div class="table-container container-fluid px-0 mb-4">
    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    @if($bookings->count() > 0)
        <div class="table-info mx-0 mt-3 px-3 d-flex justify-content-between align-items-center">
            <p class="mb-0">{{ __('messages.showing_results', ['count' => $bookings->count(), 'total' => $bookings->total()]) }}</p>
            @if(request('letter'))
                <span class="badge bg-primary px-3 py-2">{{ __('messages.filters') }}: "{{ request('letter') }}"</span>
            @endif
        </div>
        
        <table class="data-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_order' => request('sort_by') == 'name' && request('sort_order', 'asc') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center">
                            {{ __('messages.customer') }}
                            <i class="fas fa-sort{{ request('sort_by') == 'name' ? (request('sort_order') == 'asc' ? '-up' : '-down') : '' }} ms-1 opacity-50"></i>
                        </a>
                    </th>
                    <th>{{ __('messages.barcode') ?? 'Barcode' }}</th>
                    <th>{{ __('messages.phone') }}</th>
                    <th>{{ __('messages.analyses') }}</th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'analysis_date', 'sort_order' => request('sort_by') == 'analysis_date' && request('sort_order', 'desc') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center">
                            {{ __('messages.date') }}
                            <i class="fas fa-sort{{ request('sort_by') == 'analysis_date' ? (request('sort_order') == 'asc' ? '-up' : '-down') : '' }} ms-1 opacity-50"></i>
                        </a>
                    </th>
                    <th>
                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'time', 'sort_order' => request('sort_by') == 'time' && request('sort_order', 'asc') == 'asc' ? 'desc' : 'asc']) }}" class="text-decoration-none text-dark d-flex align-items-center">
                            {{ __('messages.time') }}
                            <i class="fas fa-sort{{ request('sort_by') == 'time' ? (request('sort_order') == 'asc' ? '-up' : '-down') : '' }} ms-1 opacity-50"></i>
                        </a>
                    </th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $reservation)
                <tr>
                    <td>{{ $loop->iteration + ($bookings->perPage() * ($bookings->currentPage() - 1)) }}</td>
                    <td>{{ $reservation->patient?->name }}</td>
                    <td class="barcode-cell">
                        <div class="bg-white p-1 rounded shadow-sm d-inline-block text-center" style="transform: scale(0.85); transform-origin: left center;">
                            <img src="{{ $reservation->barcode }}" width="60" alt="Barcode" style="display: block; margin-bottom: 2px; margin-left: auto; margin-right: auto;">
                            <span class="fw-bold text-uppercase text-muted" style="font-size: 6px;">Visit QR</span>
                        </div>
                    </td>
                    <td>{{ $reservation->patient?->phone }}</td>
                    <td>
                        <div class="analyses-controls">
                            @foreach($reservation->reservationAnalyses as $resAnalysis)
                                <div class="analysis-status-item mb-1">
                                    <form class="ajax-status-form" action="{{ route('admin.bookings.analysis.status.update', $resAnalysis->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="compact-status-group d-flex align-items-center">
                                            <span class="analysis-name text-truncate" title="{{ $resAnalysis->analyse->name }}">
                                                {{ $resAnalysis->analyse->name }}
                                            </span>
                                            <select name="status" class="mini-status-select status-{{ $resAnalysis->status }}">
                                                <option value="booked" {{ $resAnalysis->status == 'booked' ? 'selected' : '' }}>{{ __('messages.booked') }}</option>
                                                <option value="ready" {{ $resAnalysis->status == 'ready' ? 'selected' : '' }}>{{ __('messages.ready') }}</option>
                                                <option value="blocked" {{ $resAnalysis->status == 'blocked' ? 'selected' : '' }}>{{ __('messages.blocked') }}</option>
                                                <option value="warning" {{ $resAnalysis->status == 'warning' ? 'selected' : '' }}>{{ __('messages.warning') }}</option>
                                                <option value="pending_approval" {{ $resAnalysis->status == 'pending_approval' ? 'selected' : '' }}>{{ __('messages.pending_approval') }}</option>
                                                <option value="completed" {{ $resAnalysis->status == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td>{{ $reservation->analysis_date }}</td>
                    <td>{{ $reservation->time }}</td>
                    <td>
                        <span class="status-badge status-{{ $reservation->status }}">
                            @if($reservation->status == 'booked') {{ __('messages.booked') }}
                            @elseif($reservation->status == 'ready') {{ __('messages.ready') }}
                            @elseif($reservation->status == 'blocked') {{ __('messages.blocked') }}
                            @elseif($reservation->status == 'warning') {{ __('messages.warning') }}
                            @elseif($reservation->status == 'pending_approval') {{ __('messages.pending_approval') }}
                            @elseif($reservation->status == 'completed') {{ __('messages.completed') }}
                            @else {{ $reservation->status }} @endif
                        </span>
                        
                        <div class="mt-2 text-muted small">
                            <i class="fas fa-user-md me-1"></i>
                            <select class="referral-select ajax-referral-update" data-url="{{ route('admin.bookings.referral.update', $reservation->id) }}" style="border:none; background:transparent; font-size:11px; cursor:pointer;">
                                <option value="">— {{ __('messages.referred_by') ?? 'Assign Physician' }} —</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" {{ $reservation->doctor_id == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td class="actions-cell">
                        <div class="action-group d-flex align-items-center gap-2">
                            <form class="ajax-status-form" action="{{ route('admin.bookings.update', $reservation->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <select name="status" class="status-select status-{{ $reservation->status }}">
                                    <option value="booked" {{ $reservation->status == 'booked' ? 'selected' : '' }}>{{ __('messages.booked') }}</option>
                                    <option value="ready" {{ $reservation->status == 'ready' ? 'selected' : '' }}>{{ __('messages.ready') }}</option>
                                    <option value="blocked" {{ $reservation->status == 'blocked' ? 'selected' : '' }}>{{ __('messages.blocked') }}</option>
                                    <option value="warning" {{ $reservation->status == 'warning' ? 'selected' : '' }}>{{ __('messages.warning') }}</option>
                                    <option value="pending_approval" {{ $reservation->status == 'pending_approval' ? 'selected' : '' }}>{{ __('messages.pending_approval') }}</option>
                                    <option value="completed" {{ $reservation->status == 'completed' ? 'selected' : '' }}>{{ __('messages.completed') }}</option>
                                </select>
                            </form>
                            <a href="{{ route('admin.bookings.full-eligibility.form', $reservation->id) }}" class="btn btn-sm btn-outline-primary" title="فحص أهلية جميع التحاليل">
                                <i class="fas fa-stethoscope"></i> {{ __('messages.eligibility_check') }}
                            </a>
                            
                            @php
                                $diagStatuses = $reservation->reservationAnalyses->pluck('status');
                                $diagHasBlocked = $diagStatuses->contains('blocked');
                                $diagHasWarning = $diagStatuses->contains('warning');
                                $diagIsChecked = $diagStatuses->contains('ready') || $diagHasBlocked || $diagHasWarning;
                            @endphp

                            @if($diagIsChecked)
                                <a href="{{ route('admin.bookings.eligibility.results', $reservation->id) }}" class="btn btn-sm btn-outline-primary" title="عرض تفاصيل التقييم والإجابات">
                                    <i class="fas {{ $diagHasBlocked ? 'fa-times-circle' : ($diagHasWarning ? 'fa-exclamation-triangle' : 'fa-check-circle') }}"></i>
                                    {{ __('messages.eligibility_results') }}
                                </a>
                            @endif

                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell"></i> {{ __('messages.notify') ?? 'Notify' }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item notify-btn" href="#" data-url="{{ route('admin.bookings.notify', $reservation->id) }}" data-target="patient"><i class="fas fa-user me-2"></i> {{ __('messages.notify_patient') ?? 'Patient' }}</a></li>
                                    <li><a class="dropdown-item notify-btn" href="#" data-url="{{ route('admin.bookings.notify', $reservation->id) }}" data-target="doctor"><i class="fas fa-user-md me-2"></i> {{ __('messages.notify_doctor') ?? 'Physician' }}</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item notify-btn" href="#" data-url="{{ route('admin.bookings.notify', $reservation->id) }}" data-target="both"><i class="fas fa-users me-2"></i> {{ __('messages.notify_both') ?? 'Both' }}</a></li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
        
        <!-- Pagination -->
        <div class="pagination-container">
            {{ $bookings->appends(request()->query())->links() }}
        </div>
    @else
        <div class="no-data">
            <i class="fas fa-calendar-times"></i>
            <p>{{ __('messages.no_reservations_found') }}</p>
            <a href="{{ route('reservations') }}" class="btn btn-primary">{{ __('messages.view_all_reservations') }}</a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusForms = document.querySelectorAll('.ajax-status-form');
    
    statusForms.forEach(form => {
        const select = form.querySelector('select');
        
        select.addEventListener('change', function() {
            const formData = new FormData(form);
            const originalClass = select.className;
            
            // Add loading state
            select.disabled = true;
            select.style.opacity = '0.5';
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                select.disabled = false;
                select.style.opacity = '1';
                
                if (data.success) {
                    // Update class
                    const baseClass = select.classList.contains('mini-status-select') ? 'mini-status-select' : 'status-select';
                    select.className = `${baseClass} status-${data.status}`;
                    
                    showToast(data.message || 'تم التحديث بنجاح', 'success');
                    
                    // If it was a main reservation status update, we might want to update the badge in the same row
                    const row = select.closest('tr');
                    const mainBadge = row.querySelector('.status-badge');
                    if (mainBadge && select.classList.contains('status-select')) {
                        mainBadge.className = `status-badge status-${data.status}`;
                        // Update text if needed... simple way is to refresh the label from a hidden mapping or just leave as is
                    }
                } else {
                    showToast(data.message || 'حدث خطأ', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                select.disabled = false;
                select.style.opacity = '1';
                showToast('حدث خطأ في الاتصال بالخادم', 'danger');
            });
        });
    });

    // Referral Update Logic
    const referralSelects = document.querySelectorAll('.ajax-referral-update');
    referralSelects.forEach(select => {
        select.addEventListener('change', function() {
            const doctorId = this.value;
            const url = this.dataset.url;
            
            this.disabled = true;
            
            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ doctor_id: doctorId })
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Error', 'danger');
                }
            })
            .catch(error => {
                this.disabled = false;
                showToast('Connection error', 'danger');
            });
        });
    });

    // Notification Logic
    const notifyBtns = document.querySelectorAll('.notify-btn');
    notifyBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.dataset.url;
            const target = this.dataset.target;
            
            btn.classList.add('disabled');
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ target: target })
            })
            .then(response => response.json())
            .then(data => {
                btn.classList.remove('disabled');
                showToast(data.message, data.success ? 'success' : 'danger');
            })
            .catch(error => {
                btn.classList.remove('disabled');
                showToast('Error', 'danger');
            });
        });
    });

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} shadow-sm border-0 mb-2 py-2 px-3 fade show`;
        toast.style.minWidth = '250px';
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                <span class="small">${message}</span>
            </div>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 200);
        }, 3000);
    }
});
</script>

<style>
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .mini-status-select {
        border: none;
        background: transparent;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 12px;
        cursor: pointer;
        outline: none;
        width: 85px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .analysis-status-item {
        background: #fff;
        border: 1px solid #edf2f7;
        border-radius: 15px;
        padding: 4px 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        transition: transform 0.2s;
    }
    
    .analysis-status-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .analysis-name {
        display: inline-block;
        font-size: 11px;
        color: #4a5568;
        width: 110px;
        margin-left: 8px;
        font-weight: 600;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .compact-status-group {
        width: 100%;
        justify-content: space-between;
    }

    /* Unified Status Colors */
    .status-booked { background-color: #edf2f7 !important; color: #4a5568 !important; }
    .status-ready { background-color: #c6f6d5 !important; color: #22543d !important; }
    .status-blocked { background-color: #fed7d7 !important; color: #822727 !important; }
    .status-warning { background-color: #feebc8 !important; color: #744210 !important; }
    .status-pending_approval { background-color: #e9d8fd !important; color: #44337a !important; }
    .status-completed { background-color: #bee3f8 !important; color: #2c5282 !important; }

    .eligibility-result-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .status-select {
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .status-select:hover {
        border-color: #cbd5e0;
    }

    .actions-cell .btn {
        height: 32px;
        display: inline-flex;
        align-items: center;
        padding: 0 12px;
        font-weight: 600;
        font-size: 12px;
        white-space: nowrap;
    }
    
    .no-data {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 12px;
        color: #a0aec0;
    }
    
    .no-data i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    
    .table-info {
        background-color: #ebf8ff;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-right: 4px solid #3182ce;
        color: #2b6cb0;
        font-weight: 500;
        min-width: 1240px;
        display: block;
    }

    .toast-container .alert {
        border-right: 4px solid rgba(0,0,0,0.1);
        pointer-events: none;
    }

    /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .analysis-name { width: 90px; }
        .mini-status-select { width: 75px; }
    }

    @media (max-width: 992px) {
        .analysis-name {
            width: 100px; /* Reset width for better readability when scrolling */
        }
        .mini-status-select {
            width: 80px;
        }
        .data-table th, .data-table td {
            padding: 12px 10px;
            font-size: 13px;
        }
    }

    @media (max-width: 768px) {
        .section-header {
            flex-direction: column;
            text-align: center;
        }
        .section-header h2 {
            font-size: 1.4rem;
            margin-bottom: 10px;
        }
        .analysis-status-item {
            padding: 4px;
        }
        .analysis-name {
            width: auto;
            max-width: 120px;
            font-size: 11px;
        }
    }

    /* Force table width to prevent compression */
    .data-table {
        min-width: 900px;
    }

    .table-container {
        overflow: visible !important; /* Allow the inner responsive div to handle scrolling */
        box-shadow: none;
        border: 1px solid #e2e8f0;
    }
</style>
@endsection
