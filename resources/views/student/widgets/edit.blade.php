@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Edit Widget Settings</h2>
            <p class="text-muted">{{ ucfirst(str_replace('_', ' ', $widget->widget_type)) }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('student.widgets.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the errors below:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('student.widgets.update', $widget) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="limit" class="form-label">Items to Display</label>
                            <input type="number" class="form-control @error('limit') is-invalid @enderror" 
                                   id="limit" name="limit" min="1" max="20"
                                   value="{{ old('limit', $settings['limit'] ?? 5) }}">
                            <small class="form-text text-muted">How many items to show in this widget (1-20)</small>
                            @error('limit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show_score" name="show_score" value="1"
                                       @if(old('show_score', $settings['show_score'] ?? false)) checked @endif>
                                <label class="form-check-label" for="show_score">
                                    Show scores on quiz widgets
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Settings
                            </button>
                            <a href="{{ route('student.widgets.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
