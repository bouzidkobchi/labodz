@extends('Adminstration.layout')

@section('title', 'تفاصيل نتائج الأهلية')

@section('content')
<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('reservations') }}">الحجوزات</a></li>
                    <li class="breadcrumb-item active">نتائج الأهلية</li>
                </ol>
            </nav>
            <h2 class="fw-bold"><i class="fas fa-notes-medical text-primary me-2"></i> تقرير تقييم الأهلية الطبية</h2>
        </div>
        <a href="{{ route('reservations') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-2"></i> العودة للقائمة
        </a>
    </div>

    <!-- Patient Header -->
    <div class="card shadow-sm border-0 mb-4 bg-primary text-white overflow-hidden">
        <div class="card-body p-4 position-relative">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-1 fw-bold">{{ $reservation->patient->name }}</h3>
                    <div class="d-flex gap-4 opacity-75 small">
                        <span><i class="fas fa-phone-alt me-1"></i> {{ $reservation->patient->phone }}</span>
                        <span><i class="fas fa-venus-mars me-1"></i> {{ $reservation->patient->gender == 'male' ? 'ذكر' : 'أنثى' }}</span>
                        <span><i class="fas fa-calendar-alt me-1"></i> {{ $reservation->analysis_date }} ({{ $reservation->time }})</span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge bg-white text-primary px-3 py-2 fs-6">
                        #{{ $reservation->id }} حجز رقم
                    </span>
                </div>
            </div>
            <i class="fas fa-user-injured position-absolute end-0 top-50 translate-middle-y opacity-25 me-4 d-none d-md-block" style="font-size: 5rem;"></i>
        </div>
    </div>

    <div class="row g-4">
        <!-- Results Summary -->
        <div class="col-lg-5">
            <section class="mb-4">
                <h4 class="mb-3 fw-bold"><i class="fas fa-poll me-2 text-primary"></i> حالة التحاليل المطلوبة</h4>
                @foreach($results as $result)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0">{{ $result['name'] }}</h5>
                                <span class="badge {{ $result['status'] === 'block' ? 'bg-danger' : ($result['status'] === 'warning' ? 'bg-warning text-dark' : 'bg-success') }} px-3">
                                    {{ $result['status'] === 'block' ? 'مرفوض' : ($result['status'] === 'warning' ? 'تنبيه طبي' : 'مؤهل') }}
                                </span>
                            </div>
                            
                            @if(count($result['notes']) > 0)
                                <div class="alert {{ $result['status'] === 'block' ? 'alert-danger' : 'alert-warning' }} py-2 px-3 mb-0 small border-0">
                                    <h6 class="fw-bold mb-2 small"><i class="fas fa-info-circle me-1"></i> ملاحظات التقييم:</h6>
                                    <ul class="mb-0 ps-3">
                                        @foreach($result['notes'] as $note)
                                            <li>{{ $note }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="text-success small"><i class="fas fa-check-circle me-1"></i> مستوفي لجميع شروط البروتوكول</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        </div>

        <!-- Answer Details -->
        <div class="col-lg-7">
            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h4 class="card-title mb-0 fw-bold"><i class="fas fa-list-check me-2 text-primary"></i> إجابات المريض المفصلة</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 border-0 py-3" style="width: 50%;">السؤال</th>
                                    <th class="border-0 py-3">الإجابة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patientAnswers as $questionId => $answers)
                                    @php $question = $answers->first()->question; @endphp
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ $question->question }}</div>
                                            <small class="text-muted">{{ $question->analyse_id ? '(تحليل: ' . \App\Models\Analyse::find($question->analyse_id)->name . ')' : '' }}</small>
                                        </td>
                                        <td class="py-3">
                                            @foreach($answers as $ans)
                                                <span class="badge bg-light text-dark border px-3 py-2 mb-1">
                                                    {{ $ans->option->text }}
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-25"></i>
                                            لم يتم العثور على إجابات لهذا الحجز
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
    .breadcrumb-item + .breadcrumb-item::before { content: "←"; }
    .card { border-radius: 12px; }
    .badge { border-radius: 6px; font-weight: 600; }
    .table thead th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .text-primary { color: #3182ce !important; }
    .bg-primary { background-color: #3182ce !important; }
</style>
@endsection
