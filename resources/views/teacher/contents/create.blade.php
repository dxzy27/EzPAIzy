@extends('layouts.dashboard')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Create Content</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('teacher.contents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Content Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="topic" class="form-label">Topic</label>
                            <select name="topic" id="topic" class="form-select @error('topic') is-invalid @enderror" required>
                                <option value="" disabled selected>Select a Topic</option>
                                @foreach($topics as $t)
                                    <option value="{{ $t->name }}" {{ (old('topic') ?? request('topic')) == $t->name ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('topic')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Upload File (Optional)</label>
                            <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file">
                            <div class="form-text">Supported types: Images, PDF, Word, PowerPoint, Video. Max 100MB.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Description / Text Content</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Create Content</button>
                            <a href="{{ route('teacher.contents.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
