@extends('layouts.dashboard')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="text-center mb-5">
                <h1 class="fw-bold"><i class="bi bi-filetype-pdf text-danger me-2"></i>PDF to Question Bank</h1>
                <p class="text-muted lead">Upload your module PDF and we'll extract all questions and answers into a downloadable CSV file.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-5">

                    <form action="{{ route('teacher.question-bank.extract') }}" method="POST" enctype="multipart/form-data" id="extractForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold fs-5">
                                <i class="bi bi-upload me-2 text-primary"></i>Upload PDF Module
                            </label>
                            <input type="file"
                                   name="pdf"
                                   id="pdfInput"
                                   class="form-control form-control-lg @error('pdf') is-invalid @enderror"
                                   accept=".pdf"
                                   required>
                            @error('pdf')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum file size: 20MB. The PDF must contain selectable text (not a scanned image).</small>
                        </div>

                        
                        <div id="filePreview" class="d-none mb-4">
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-4 me-3"></i>
                                <div>
                                    <strong id="fileName"></strong>
                                    <div class="text-muted small" id="fileSize"></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" id="submitBtn" class="btn btn-primary btn-lg rounded-pill">
                                <i class="bi bi-magic me-2"></i>Extract Questions to CSV
                            </button>
                        </div>
                    </form>

                    
                    <div id="loadingState" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                        <h5 class="fw-bold">Extracting questions...</h5>
                        <p class="text-muted">Gemini AI is reading your module and structuring the questions. This may take 15–30 seconds.</p>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-info me-2"></i>How it works</h5>
                    <ol class="mb-0">
                        <li class="mb-2">Upload your MRSM or any module PDF that contains questions and answers.</li>
                        <li class="mb-2">AI reads the text and extracts every question with its answer.</li>
                        <li class="mb-2">A <strong>CSV file</strong> is automatically downloaded with columns: <code>topic, question, answer</code>.</li>
                        <li>You can review and edit it in Excel/Google Sheets before using it.</li>
                    </ol>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-table text-success me-2"></i>CSV Output Format</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small">
                            <thead class="table-dark">
                                <tr>
                                    <th>topic</th>
                                    <th>question</th>
                                    <th>answer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-muted">
                                    <td>Aqidah</td>
                                    <td>Apakah maksud iman kepada Allah?</td>
                                    <td>Mempercayai kewujudan Allah dengan sepenuh hati...</td>
                                </tr>
                                <tr class="text-muted">
                                    <td>Akhlak</td>
                                    <td>Nyatakan dua ciri akhlak mahmudah.</td>
                                    <td>Sabar dan amanah.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // File preview
    document.getElementById('pdfInput').addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            document.getElementById('filePreview').classList.remove('d-none');
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        }
    });

    // Show loading on submit
    document.getElementById('extractForm').addEventListener('submit', function () {
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        document.getElementById('loadingState').classList.remove('d-none');
        document.getElementById('extractForm').querySelector('.d-grid').classList.add('d-none');
    });
</script>
@endsection
