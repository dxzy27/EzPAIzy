@extends('layouts.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

@section('content')
<div class="container mt-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                    <i class="bi bi-house-door fs-5"></i>
                </a>
                <div>
                    <h2 class="mb-0">Customize Your Dashboard</h2>
                    <p class="text-muted mb-0">Add or remove widgets to personalize your learning experience</p>
                </div>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Available Widgets -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Available Widgets</h5>
                </div>
                <div class="card-body">
                    @forelse ($availableWidgets as $key => $widget)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title">{{ $widget['name'] }}</h6>
                                <p class="card-text text-muted small">{{ $widget['description'] }}</p>
                                @if (in_array($key, $activeWidgets))
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Added
                                    </span>
                                @else
                                    <form action="{{ route('student.widgets.store') }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="widget_type" value="{{ $key }}">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-plus-circle"></i> Add Widget
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No widgets available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Active Widgets -->
<!-- Active Widgets -->
<div class="col-md-6">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Active Widgets ({{ count($activeWidgets) }})</h5>
        </div>
        <div class="card-body">
            @if (count($activeWidgets) > 0)
                @php $user = auth()->user(); @endphp
                @foreach ($user->widgets as $widget)
                    <div class="card mb-3 border-success">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $availableWidgets[$widget->widget_type]['name'] ?? ucfirst(str_replace('_', ' ', $widget->widget_type)) }}</h6>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('student.widgets.edit', $widget) }}" class="btn btn-primary" title="Edit settings">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('student.widgets.destroy', $widget) }}" method="POST" style="display:inline;" onsubmit="return confirm('Remove this widget?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-muted">
                    <i class="bi bi-info-circle"></i> No widgets added yet. Add widgets from the left panel to customize your dashboard.
                </p>
            @endif
        </div>
    </div>
</div>

    </div>
</div>
@endsection

<style>
    /* Ensuring the buttons are in the same line */
    .btn-group-sm {
        display: flex;
        gap: 5px;  /* Ensures space between the buttons */
    }

    .btn-group-sm .btn {
        padding: 5px 10px; /* Adjust button padding for uniform size */
        margin: 0;  /* Remove extra margin */
    }

    /* Align items properly */
    .card-body .d-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
