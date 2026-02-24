@extends('Adminstration.layout')

@section('title', __('messages.manage_eligibility'))

@section('styles')
<style>
    .question-card {
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        margin-bottom: 24px;
        transition: all 0.3s ease;
        background: #fff;
        overflow: hidden;
    }
    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .question-header {
        background: #f8f9fa;
        padding: 16px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .question-title {
        font-weight: 600;
        margin: 0;
        color: #2c3e50;
    }
    .options-list {
        padding: 20px;
    }
    .option-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-radius: 8px;
        background: #fdfdfd;
        border: 1px solid #f0f0f0;
        margin-bottom: 10px;
    }
    .option-text {
        flex-grow: 1;
    }
    .badge-action {
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 20px;
        margin-right: 10px;
        margin-left: 10px;
    }
    .badge-block { background-color: #fceaea; color: #d93025; }
    .badge-warning { background-color: #fef7e0; color: #f29900; }
    .badge-approval { background-color: #e8f0fe; color: #1a73e8; }
    .badge-none { background-color: #e6f4ea; color: #1e8e3e; }

    .add-form {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-clipboard-check me-2"></i> {{ __('messages.eligibility_for', ['name' => $analysis->name]) }}</h2>
        <a href="{{ route('analyses') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back') }}
        </a>
    </div>

    <!-- Add Question Form -->
    <div class="add-form">
        <h5 class="mb-3"><i class="fas fa-plus-circle me-1"></i> {{ __('messages.add_question') }}</h5>
        <form action="{{ route('eligibility.questions.store', $analysis->id) }}" method="POST">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-9">
                    <label class="form-label">{{ __('messages.question_text') }}</label>
                    <input type="text" name="question" class="form-control" placeholder="مثلاً: هل أنت صائم؟" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 mt-3 mt-md-0">
                        <i class="fas fa-save me-1"></i> {{ __('messages.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Questions List -->
    <div class="questions-container">
        @forelse($analysis->questions as $question)
            <div class="question-card">
                <div class="question-header">
                    <h5 class="question-title">
                        <span class="text-muted me-2">#{{ $loop->iteration }}</span>
                        {{ $question->question }}
                    </h5>
                    <form action="{{ route('eligibility.questions.destroy', $question->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_question_confirm') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
                <div class="options-list">
                    <h6 class="mb-3 text-muted">{{ __('messages.options') }}</h6>
                    
                    @if($question->options->count() > 0)
                        @foreach($question->options as $option)
                            <div class="option-item">
                                <span class="option-text">{{ $option->text }}</span>
                                
                                @php $rule = $rules->get($option->id)?->first(); @endphp
                                
                                @if($rule)
                                    <span class="badge-action badge-{{ $rule->action }}">
                                        {{ __('messages.' . $rule->action) }}
                                    </span>
                                @else
                                    <span class="badge-action badge-none">
                                        {{ __('messages.no_action') }}
                                    </span>
                                @endif

                                <form action="{{ route('eligibility.options.destroy', $option->id) }}" method="POST" onsubmit="return confirm('{{ __('messages.delete_option_confirm') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs text-danger ms-2">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    @else
                        <p class="small text-muted italic">{{ __('messages.no_options_added') ?? 'No options added yet.' }}</p>
                    @endif

                    <hr class="my-3">

                    <!-- Add Option Form -->
                    <form action="{{ route('eligibility.options.store', $question->id) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small">{{ __('messages.option_text') }}</label>
                                <input type="text" name="text" class="form-control form-control-sm" placeholder="Oui / Non / ... " required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('messages.rule_action') }}</label>
                                <select name="action" class="form-select form-select-sm">
                                    <option value="none">{{ __('messages.no_action') }}</option>
                                    <option value="warning">{{ __('messages.warning') }}</option>
                                    <option value="block">{{ __('messages.block') }}</option>
                                    <option value="approval">{{ __('messages.approval_required') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="fas fa-plus me-1"></i> {{ __('messages.add_option') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                <p>{{ __('messages.no_questions_added') ?? 'No eligibility questions added yet.' }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
