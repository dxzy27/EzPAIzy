@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Edit Content</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('teacher.contents.update', $content) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Content Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $content->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="topic" class="form-label">Topic</label>
                            <select name="topic" id="topic" class="form-select @error('topic') is-invalid @enderror" required>
                                <option value="" disabled>Select a Topic</option>
                                @foreach($topics as $t)
                                    <option value="{{ $t->name }}" {{ old('topic', $content->topic ?? '') == $t->name ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Attachment</label>
                            @if($content->file_path)
                                <div class="mb-2 p-2 border rounded bg-light">
                                    <i class="bi bi-paperclip me-2"></i> Current file: {{ basename($content->file_path) }}
                                </div>
                            @endif
                            <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file">
                            <div class="form-text">Upload to replace existing file. Supported types: Images, PDF, Word, PowerPoint, Video. Max 20MB.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Description / Text Content (Optional)</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6">{{ old('content', $content->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Update Content</button>
                            <a href="{{ route('teacher.contents.show', $content) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
