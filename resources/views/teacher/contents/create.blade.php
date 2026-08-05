@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 1040px; margin: 0 auto;">
    <!-- Top Navigation Header -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ url()->previous() === url()->current() ? route('teacher.contents.index') : url()->previous() }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back">
            <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <div>
            <h2 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">Upload Material</h2>
            <p class="text-muted mb-0" style="font-size: 0.88rem;">Share course notes, PDFs, or learning resources with your students</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 16px;">
        <div class="card-body p-4">
            <form action="{{ route('teacher.contents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3 mb-3">
                    <!-- Title -->
                    <div class="col-md-8">
                        <label for="title" class="form-label text-muted small fw-bold text-uppercase mb-1">Content Title</label>
                        <input type="text" class="form-control form-control-lg fw-semibold @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. Chapter 1 Summary Notes" required style="border-radius: 10px; font-size: 1.05rem;">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Topic -->
                    <div class="col-md-4">
                        <label for="topic" class="form-label text-muted small fw-bold text-uppercase mb-1">Topic</label>
                        <select name="topic" id="topic" class="form-select form-select-lg fw-semibold @error('topic') is-invalid @enderror" required style="border-radius: 10px; font-size: 0.95rem;">
                            <option value="" disabled selected>Select Topic</option>
                            @foreach($topics as $t)
                                <option value="{{ $t->name }}" {{ (old('topic') ?? request('topic')) == $t->name ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('topic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Attachment Upload -->
                    <div class="col-12">
                        <label for="file" class="form-label text-muted small fw-bold text-uppercase mb-1">Upload File (Optional)</label>
                        <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file" style="border-radius: 10px; padding: 10px 14px;">
                        <div class="form-text mt-1 text-muted" style="font-size: 0.8rem;">
                            <i class="bi bi-info-circle me-1"></i> Supported formats: Images, PDF, Word, PowerPoint, Video (Max 100MB).
                        </div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @error
                    </div>

                    <!-- Text Content / Description -->
                    <div class="col-12">
                        <label for="content" class="form-label text-muted small fw-bold text-uppercase mb-1">Description / Text Content (Optional)</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="5" placeholder="Add study notes, instructions, or reading guidelines..." style="resize: none; border-radius: 10px;">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="d-flex justify-content-end align-items-center gap-2 pt-3 border-top mt-4">
                    <a href="{{ route('teacher.contents.index') }}" class="btn btn-light fw-semibold px-4" style="border-radius: 10px;">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4" style="border-radius: 10px; box-shadow: 0 4px 12px rgba(59,130,246,0.3);">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload Content
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
