@extends('Adminstration.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>{{ __('messages.reservation_requests') }}</h2>
        <a href="{{ route('reservations') }}" class="btn btn-secondary">{{ __('messages.confirmed_reservations') }}</a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>{{ __('messages.filter_requests') }}</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reservation.requests') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">{{ __('messages.status') }}</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>{{ __('messages.confirm') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('messages.rejected') }}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">{{ __('messages.search') }}</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('messages.search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">{{ __('messages.filters') }}</button>
                    <a href="{{ route('reservation.requests') }}" class="btn btn-outline-secondary">{{ __('messages.reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-header">
            <h5>{{ __('messages.requests_list', ['total' => $requests->total()]) }}</h5>
        </div>
        <div class="card-body">
            @if($requests->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('messages.name') }}</th>
                            <th>{{ __('messages.phone') }}</th>
                            <th>{{ __('messages.email') }}</th>
                            <th>{{ __('messages.gender') }}</th>
                            <th>{{ __('messages.birth_date') }}</th>
                            <th>{{ __('messages.analysis_types') }}</th>
                            <th>{{ __('messages.prescription') }}</th>
                            <th>{{ __('messages.preferred_date') }}</th>
                            <th>{{ __('messages.preferred_time') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.request_date') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $requestItem)
                        <tr>
                            <td>{{ $requestItem->name }}</td>
                            <td>{{ $requestItem->phone }}</td>
                            <td>{{ $requestItem->email ?? 'غير متوفر' }}</td>
                            <td>{{ $requestItem->gender == 'male' ? __('messages.male') : ($requestItem->gender == 'female' ? __('messages.female') : __('messages.unavailable')) }}</td>
                            <td>{{ $requestItem->birth_date ? \Carbon\Carbon::parse($requestItem->birth_date)->format('Y-m-d') : 'غير محدد' }}</td>
                            <td>
                                @if($requestItem->analyses->count() > 0)
                                    @foreach($requestItem->analyses as $analyse)
                                        <span class="badge bg-info">{{ $analyse->name }}</span>
                                    @endforeach
                                @else
                                    {{ $requestItem->analyse->name ?? 'غير متوفر' }}
                                @endif
                            </td>
                            <td>
                                @if($requestItem->prescription_path)
                                    <a href="{{ asset('storage/' . $requestItem->prescription_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-file-medical"></i> {{ __('messages.view_prescription') }}
                                    </a>
                                @else
                                    <span class="text-muted small">{{ __('messages.no_prescription') }}</span>
                                @endif
                            </td>
                            <td>{{ $requestItem->preferred_date ? \Carbon\Carbon::parse($requestItem->preferred_date)->format('Y-m-d') : 'غير محدد' }}</td>
                            <td>{{ $requestItem->preferred_time ? substr($requestItem->preferred_time, 0, 5) : 'غير محدد' }}</td>
                            <td>
                                @if($requestItem->status == 'pending')
                                <span class="badge bg-warning">{{ __('messages.pending') }}</span>
                                @elseif($requestItem->status == 'confirmed')
                                <span class="badge bg-success">{{ __('messages.confirm') }}</span>
                                @elseif($requestItem->status == 'rejected')
                                <span class="badge bg-danger">{{ __('messages.rejected') }}</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($requestItem->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($requestItem->status == 'pending')
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal{{ $requestItem->id }}">
                                    {{ __('messages.confirm') }}
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $requestItem->id }}">
                                    {{ __('messages.reject') }}
                                </button>
                                @else
                                @if($requestItem->admin_notes)
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#notesModal{{ $requestItem->id }}">
                                    {{ __('messages.admin_notes') }}
                                </button>
                                @endif
                                @if($requestItem->patient_id)
                                <a href="{{ route('reservations') }}?search={{ $requestItem->name }}" class="btn btn-primary btn-sm">
                                    {{ __('messages.view_reservation') }}
                                </a>
                                @endif
                                @endif
                            </td>
                        </tr>

                        <!-- Confirm Modal -->
                        @if($requestItem->status == 'pending')
                        <div class="modal fade" id="confirmModal{{ $requestItem->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('messages.confirm_modal_title') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('reservation.requests.confirm', $requestItem->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('messages.patient_name') }}: {{ $requestItem->name }}</label>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('messages.requested_analyses') }}:</label>
                                                @if($requestItem->analyses->count() > 0)
                                                    <ul>
                                                        @foreach($requestItem->analyses as $analyse)
                                                            <li>{{ $analyse->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p>{{ $requestItem->analyse->name ?? 'غير متوفر' }}</p>
                                                @endif
                                            </div>
                                            @if($requestItem->prescription_path)
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('messages.attached_prescription') }}</label>
                                                <div class="p-2 border rounded bg-light text-center">
                                                    <a href="{{ asset('storage/' . $requestItem->prescription_path) }}" target="_blank" class="d-block">
                                                        <img src="{{ asset('storage/' . $requestItem->prescription_path) }}" alt="Prescription" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                                    </a>
                                                    <div class="mt-2">
                                                        <a href="{{ asset('storage/' . $requestItem->prescription_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-external-link-alt"></i> {{ __('messages.view_full') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="mb-3">
                                                <label for="analysis_date{{ $requestItem->id }}" class="form-label">{{ __('messages.analysis_date_label') }}</label>
                                                <input type="date" name="analysis_date" id="analysis_date{{ $requestItem->id }}" class="form-control"
                                                    value="{{ $requestItem->preferred_date ? \Carbon\Carbon::parse($requestItem->preferred_date)->format('Y-m-d') : date('Y-m-d') }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="time{{ $requestItem->id }}" class="form-label">{{ __('messages.analysis_time_label') }}</label>
                                                <input type="time" name="time" id="time{{ $requestItem->id }}" class="form-control"
                                                    value="{{ $requestItem->preferred_time ? substr($requestItem->preferred_time, 0, 5) : '09:00' }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="admin_notes{{ $requestItem->id }}" class="form-label">{{ __('messages.admin_notes') }}</label>
                                                <textarea name="admin_notes" id="admin_notes{{ $requestItem->id }}" class="form-control" rows="3" placeholder="{{ __('messages.admin_notes') }}..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                            <button type="submit" class="btn btn-success">{{ __('messages.confirm_booking') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div class="modal fade" id="rejectModal{{ $requestItem->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('messages.reject_modal_title') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('reservation.requests.reject', $requestItem->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">{{ __('messages.reject_confirmation', ['name' => $requestItem->name]) }}</label>
                                            </div>
                                            <div class="mb-3">
                                                <label for="reject_notes{{ $requestItem->id }}" class="form-label">{{ __('messages.rejection_reason') }}</label>
                                                <textarea name="admin_notes" id="reject_notes{{ $requestItem->id }}" class="form-control" rows="3" required
                                                    placeholder="{{ __('messages.rejection_reason') }}"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                                            <button type="submit" class="btn btn-danger">{{ __('messages.reject') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Notes Modal -->
                        @if($requestItem->admin_notes)
                        <div class="modal fade" id="notesModal{{ $requestItem->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('messages.admin_notes') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>{{ $requestItem->admin_notes }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $requests->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <h4>{{ __('messages.no_requests') }}</h4>
                <p class="text-muted">{{ __('messages.no_requests_desc') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
