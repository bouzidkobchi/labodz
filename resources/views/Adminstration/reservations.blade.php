@extends('Adminstration.layout')

@section('title', 'إدارة الحجوزات')

@section('content')
<div class="section-header d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-calendar-check"></i> إدارة الحجوزات</h2>
    <div id="toast-container" class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 9999;"></div>
</div>

<!-- Filters -->
<div class="filters-container">
    <h3><i class="fas fa-filter"></i> تصفية الحجوزات</h3>
    <form method="GET" action="{{ route('filter.reservations') }}">
        <div class="filter-row">
            <div class="form-group">
                <label>من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>
            <div class="form-group">
                <label>إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>
            <div class="form-group">
                <label>حالة الحجز</label>
                <select name="status" class="form-control">
                    <option value="">جميع الحالات</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>مؤكد</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>منتهي</option>
                </select>
            </div>
            <div class="form-group">
                <label>بحث</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="بحث بالاسم أو الهاتف">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> تطبيق التصفية
        </button>
        <a href="{{ route('reservations') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> إلغاء
        </a>
    </form>
</div>

<!-- Results Table -->
<div class="table-container container-fluid px-0 mb-4">
    <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    @if($bookings->count() > 0)
        <div class="table-info mx-0 mt-3 px-3">
            <p class="mb-0">عرض {{ $bookings->count() }} من أصل {{ $bookings->total() }} حجز</p>
        </div>
        
        <table class="data-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>التحاليل</th>
                    <th>التاريخ</th>
                    <th>الوقت</th>
                    <th>حالة الموعد</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $reservation)
                <tr>
                    <td>{{ $loop->iteration + ($bookings->perPage() * ($bookings->currentPage() - 1)) }}</td>
                    <td>{{ $reservation->patient->name }}</td>
                    <td>{{ $reservation->patient->phone }}</td>
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
                                                <option value="booked" {{ $resAnalysis->status == 'booked' ? 'selected' : '' }}>محجوز</option>
                                                <option value="ready" {{ $resAnalysis->status == 'ready' ? 'selected' : '' }}>جاهز</option>
                                                <option value="blocked" {{ $resAnalysis->status == 'blocked' ? 'selected' : '' }}>محظور</option>
                                                <option value="warning" {{ $resAnalysis->status == 'warning' ? 'selected' : '' }}>تنبيه</option>
                                                <option value="pending_approval" {{ $resAnalysis->status == 'pending_approval' ? 'selected' : '' }}>بانتظار</option>
                                                <option value="completed" {{ $resAnalysis->status == 'completed' ? 'selected' : '' }}>منتهي</option>
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
                            @if($reservation->status == 'booked') محجوز
                            @elseif($reservation->status == 'ready') جاهز
                            @elseif($reservation->status == 'blocked') محظور
                            @elseif($reservation->status == 'warning') تنبيه
                            @elseif($reservation->status == 'pending_approval') بانتظار الموافقة
                            @elseif($reservation->status == 'completed') منتهي
                            @else {{ $reservation->status }} @endif
                        </span>
                    </td>
                    <td class="actions-cell">
                        <div class="action-group d-flex align-items-center gap-2">
                            <form class="ajax-status-form" action="{{ route('admin.bookings.update', $reservation->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PUT')
                                <select name="status" class="status-select status-{{ $reservation->status }}">
                                    <option value="booked" {{ $reservation->status == 'booked' ? 'selected' : '' }}>محجوز</option>
                                    <option value="ready" {{ $reservation->status == 'ready' ? 'selected' : '' }}>جاهز</option>
                                    <option value="blocked" {{ $reservation->status == 'blocked' ? 'selected' : '' }}>محظور</option>
                                    <option value="warning" {{ $reservation->status == 'warning' ? 'selected' : '' }}>تنبيه</option>
                                    <option value="pending_approval" {{ $reservation->status == 'pending_approval' ? 'selected' : '' }}>بانتظار الموافقة</option>
                                    <option value="completed" {{ $reservation->status == 'completed' ? 'selected' : '' }}>منتهي</option>
                                </select>
                            </form>
                            <a href="{{ route('admin.bookings.full-eligibility.form', $reservation->id) }}" class="btn btn-sm btn-outline-primary" title="فحص أهلية جميع التحاليل">
                                <i class="fas fa-stethoscope"></i> فحص الأهلية
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
                                    نتائج الأهلية
                                </a>
                            @endif
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
            <p>لا توجد حجوزات تطابق معايير البحث</p>
            <a href="{{ route('reservations') }}" class="btn btn-primary">عرض جميع الحجوزات</a>
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
