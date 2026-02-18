@extends('Adminstration.layout')

@section('title', 'تقييم الأهلية الطبية')

@section('content')
<div class="section-header">
    <h2><i class="fas fa-stethoscope"></i> تقييم الأهلية الطبية الموحد</h2>
    <p>المريض: <strong>{{ $patient->name }}</strong> | حجز موعد: <strong>{{ $reservation->analysis_date }} {{ $reservation->time }}</strong></p>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        @php
            $hasQuestions = false;
            foreach($reservation->reservationAnalyses as $resAnalysis) {
                if($resAnalysis->analyse->questions->count() > 0) {
                    $hasQuestions = true;
                    break;
                }
            }
        @endphp

        @if($hasQuestions)
            <form id="eligibility-form" action="{{ route('admin.bookings.full-eligibility.submit', $reservation->id) }}" method="POST">
                @csrf
                
                <div class="analyses-questions">
                    @foreach($reservation->reservationAnalyses as $resAnalysis)
                        @if($resAnalysis->analyse->questions->count() > 0)
                            <div class="analysis-group mb-5 p-3 border rounded bg-light">
                                <h4 class="mb-4 text-dark border-bottom pb-2">
                                    <i class="fas fa-microscope text-info me-2"></i>
                                    {{ $resAnalysis->analyse->name }}
                                </h4>
                                
                                @foreach($resAnalysis->analyse->questions->where('parent_question_id', null) as $question)
                                    @php
                                        $shouldShow = true;
                                        if ($question->gender_condition && strtolower($patient->gender) !== strtolower($question->gender_condition)) {
                                            $shouldShow = false;
                                        }
                                    @endphp
                                    
                                    <div class="question-wrapper {{ !$shouldShow ? 'd-none' : '' }}" 
                                         data-question-id="{{ $question->id }}"
                                         data-gender-condition="{{ $question->gender_condition }}">
                                        
                                        <div class="question-item mb-4 pb-3 border-bottom">
                                            <h5 class="mb-3 text-primary">{{ $question->question }}</h5>
                                            <div class="options-container d-flex flex-wrap gap-4">
                                                @foreach($question->options as $option)
                                                    <div class="form-check custom-input">
                                                        @if($question->is_multiple)
                                                            <input class="form-check-input eligibility-option" type="checkbox" 
                                                                   name="answers[{{ $question->id }}][]" 
                                                                   id="option_{{ $option->id }}" 
                                                                   value="{{ $option->id }}"
                                                                   data-option-value="{{ $option->value }}">
                                                        @else
                                                            <input class="form-check-input eligibility-option" type="radio" 
                                                                   name="answers[{{ $question->id }}]" 
                                                                   id="option_{{ $option->id }}" 
                                                                   value="{{ $option->id }}" 
                                                                   data-option-value="{{ $option->value }}"
                                                                   required>
                                                        @endif
                                                        <label class="form-check-label px-2" for="option_{{ $option->id }}">
                                                            {{ $option->text }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Sub-questions --}}
                                        @foreach($question->subQuestions as $subQuestion)
                                            <div class="sub-question-wrapper d-none mb-4 ms-4 p-3 border-start border-info bg-white rounded shadow-sm"
                                                 data-parent-option-id="{{ $subQuestion->show_when_option_id }}">
                                                <h6 class="mb-3 text-info"><i class="fas fa-level-down-alt me-2 fa-rotate-180"></i>{{ $subQuestion->question }}</h6>
                                                <div class="options-container d-flex flex-wrap gap-4">
                                                    @foreach($subQuestion->options as $option)
                                                        <div class="form-check custom-input">
                                                            <input class="form-check-input eligibility-option" type="radio" 
                                                                   name="answers[{{ $subQuestion->id }}]" 
                                                                   id="option_{{ $option->id }}" 
                                                                   value="{{ $option->id }}"
                                                                   data-option-value="{{ $option->value }}">
                                                            <label class="form-check-label px-2" for="option_{{ $option->id }}">
                                                                {{ $option->text }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-2">
                    <a href="{{ route('reservations') }}" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-right me-2"></i> إلغاء العودة
                    </a>
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" id="submit-check" class="btn btn-primary btn-lg px-5 shadow">
                            <i class="fas fa-check-circle me-2"></i> تأكيد التقييم لجميع التحاليل
                        </button>
                        <div id="loading-spinner" class="d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Results Container -->
            <div id="results-container" class="mt-5 d-none">
                <hr class="my-5">
                <h3 class="mb-4 text-center"><i class="fas fa-poll-h me-2"></i> نتائج التقييم النهائي</h3>
                <div id="results-list" class="row g-4">
                    <!-- Results will be injected here -->
                </div>
                <div class="text-center mt-5">
                    <a href="{{ route('reservations') }}" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-tasks me-2"></i> العودة لإدارة الحجوزات
                    </a>
                </div>
            </div>
        @else
            <div class="no-questions text-center py-5">
                <i class="fas fa-info-circle fa-4x text-muted mb-3"></i>
                <h4 class="text-secondary">لا توجد أسئلة بروتوكولية للتحاليل في هذا الحجز</h4>
                <p class="text-muted">هذه التحاليل لا تتطلب فحص أهلية خاص. يمكنك العودة لإدارة الحجوزات.</p>
                <a href="{{ route('reservations') }}" class="btn btn-primary mt-3 px-4">العودة للحجوزات</a>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('eligibility-form');
    if (!form) return;

    // Handle conditional sub-questions
    const handleSubQuestions = (radio) => {
        const questionWrapper = radio.closest('.question-wrapper');
        if (!questionWrapper) return;

        const subQuestions = questionWrapper.querySelectorAll('.sub-question-wrapper');
        subQuestions.forEach(sub => {
            if (radio.checked && sub.dataset.parentOptionId == radio.value) {
                sub.classList.remove('d-none');
                sub.querySelectorAll('input').forEach(input => input.required = true);
            } else if (radio.type === 'radio' && radio.name === `answers[${questionWrapper.dataset.questionId}]`) {
                // Only hide if the changed radio is on the same parent and it's NOT the triggering one
                sub.classList.add('d-none');
                sub.querySelectorAll('input').forEach(input => {
                    input.required = false;
                    input.checked = false;
                });
            }
        });
    };

    form.querySelectorAll('.eligibility-option').forEach(input => {
        if (input.type === 'radio' && input.checked) handleSubQuestions(input);
        
        input.addEventListener('change', function() {
            if (this.type === 'radio') {
                handleSubQuestions(this);
            }
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submit-check');
        const spinner = document.getElementById('loading-spinner');
        const resultsContainer = document.getElementById('results-container');
        const resultsList = document.getElementById('results-list');
        
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || 'Server error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
            resultsContainer.classList.remove('d-none');
            resultsList.innerHTML = '';
            
            if (data.results && data.results.length > 0) {
                data.results.forEach(result => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6';
                    
                    let cardClass = 'bg-light';
                    let iconClass = 'fa-check-circle text-success';
                    let statusText = 'جاهز للتنفيذ';
                    
                    if (result.status === 'blocked') {
                        cardClass = 'border-danger bg-danger-subtle';
                        iconClass = 'fa-times-circle text-danger';
                        statusText = 'محظور / إعادة جدولة';
                    } else if (result.status === 'warning') {
                        cardClass = 'border-warning bg-warning-subtle';
                        iconClass = 'fa-exclamation-triangle text-warning';
                        statusText = 'تنبيه / مراجعة';
                    } else if (result.status === 'pending_approval') {
                        cardClass = 'border-info bg-info-subtle';
                        iconClass = 'fa-clock text-info';
                        statusText = 'بانتظار الموافقة';
                    }
                    
                    let notesHtml = '';
                    if (result.notes && result.notes.length > 0) {
                        notesHtml = '<ul class="mt-2 mb-0 small text-muted">';
                        result.notes.forEach(note => {
                            notesHtml += `<li><i class="fas fa-caret-left me-1"></i> ${note}</li>`;
                        });
                        notesHtml += '</ul>';
                    }

                    col.innerHTML = `
                        <div class="card h-100 border-2 shadow-sm ${cardClass}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">${result.name}</h5>
                                    <i class="fas ${iconClass} fa-2x"></i>
                                </div>
                                <p class="card-text mb-1">
                                    <strong>الحالة:</strong> ${statusText}
                                </p>
                                ${notesHtml}
                            </div>
                        </div>
                    `;
                    resultsList.appendChild(col);
                });
                
                // Scroll to results
                resultsContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                resultsList.innerHTML = '<div class="col-12 text-center text-muted">لا توجد نتائج تقييم.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
            alert('حدث خطأ أثناء معالجة الطلب: ' + error.message);
        });
    });
});
</script>

<style>
    .bg-danger-subtle { background-color: #fff5f5; border-color: #feb2b2; }
    .bg-warning-subtle { background-color: #fffaf0; border-color: #fbd38d; }
    .bg-info-subtle { background-color: #ebf8ff; border-color: #90cdf4; }
    
    .section-header h2 {
        color: #2d3748;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .question-item h5 {
        font-weight: 600;
        line-height: 1.5;
    }
    .custom-radio .form-check-input:checked {
        background-color: #3182ce;
        border-color: #3182ce;
    }
    .custom-radio .form-check-label {
        font-size: 1.1rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    .custom-radio:hover .form-check-label {
        color: #3182ce;
    }
    .btn-primary {
        background-color: #3182ce;
        border-color: #3182ce;
    }
    .btn-primary:hover {
        background-color: #2b6cb0;
        border-color: #2b6cb0;
    }
    
    #results-container {
        animation: fadeInUp 0.5s ease-out;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
