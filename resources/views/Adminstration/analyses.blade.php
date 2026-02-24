@extends('Adminstration.layout')

@section('title', __('messages.manage_analyses'))

@section('content')
<div class="section-header">
    <h2><i class="fas fa-flask"></i> {{ __('messages.manage_analyses') }}</h2>
    <a href="{{ route('analyses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> {{ __('messages.add_new_analysis') }}
    </a>
</div>

<!-- Statistics Cards -->
<div class="stats-cards">
    <div class="analyses-stat-card">
        <div class="stat-icon">
            <i class="fas fa-flask"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $analyses->total() }}</h3>
            <p>{{ __('messages.total_analyses') }}</p>
        </div>
    </div>
    <div class="analyses-stat-card">
        <div class="stat-icon available">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3>{{ App\Models\Analyse::where('availability', 1)->count() }}</h3>
            <p>{{ __('messages.available_analyses') }}</p>
        </div>
    </div>
    <div class="analyses-stat-card">
        <div class="stat-icon unavailable">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-info">
            <h3>{{ App\Models\Analyse::where('availability', 0)->count() }}</h3>
            <p>{{ __('messages.unavailable_analyses') }}</p>
        </div>
    </div>
</div>

<!-- Analyses Table -->
<div class="table-container">
    @if($analyses->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.image') }}</th>
                    <th>{{ __('messages.analysis_name') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.price') }} ({{ __('messages.price_unit', ['unit' => 'دج']) ?? 'دج' }})</th>
                    <th>{{ __('messages.duration') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analyses as $analysis)
                <tr>
                    <td>{{ $loop->iteration + ($analyses->perPage() * ($analyses->currentPage() - 1)) }}</td>
                    <td>
                        @if($analysis->image)
                            <img src="{{ Storage::disk('public')->url($analysis->image) }}"
                                 alt="{{ $analysis->name }}"
                                 class="analysis-image">
                        @else
                            <div class="no-image">
                                <i class="fas fa-flask"></i>
                            </div>
                        @endif
                    </td>
                    <td>{{ $analysis->name }}</td>
                    <td class="description-cell">
                        <span class="description-text">{{ Str::limit($analysis->description, 50) }}</span>
                        @if(strlen($analysis->description) > 50)
                            <button class="btn-show-more" onclick="toggleDescription(this)">{{ __('messages.show_more') }}</button>
                        @endif
                    </td>
                    <td>{{ number_format($analysis->price, 0, ',', '.') }}</td>
                    <td>{{ $analysis->duration }}</td>
                    <td>
                        <form action="{{ route('analyses.toggle-availability', $analysis->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="status-toggle {{ $analysis->availability ? 'available' : 'unavailable' }}">
                                <i class="fas {{ $analysis->availability ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>

                                {{ $analysis->availability ? __('messages.available') : __('messages.unavailable') }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('eligibility.manage', $analysis->id) }}" class="btn btn-info" title="{{ __('messages.manage_eligibility') }}">
                                <i class="fas fa-clipboard-check"></i>
                            </a>
                            <a href="{{ route('analyses.edit', $analysis->id) }}" class="btn btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('analyses.destroy', $analysis->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete" onclick="return confirm('{{ __('messages.delete_analysis_confirm') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $analyses->links() }}
        </div>
    @else
        <div class="no-data">
            <i class="fas fa-flask"></i>
            <p>{{ __('messages.no_analyses_added') }}</p>
        </div>
    @endif
</div>

@endsection
