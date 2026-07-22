@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-5" style="min-height: 100vh; background-color: transparent;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Dashboard">
                        <i class="bi bi-house-door fs-5"></i>
                    </a>
                    <div>
                        <h1 class="h2 fw-bold text-dark mb-0">Daily Doa</h1>
                        <p class="text-muted mb-0">Your daily dose of supplications and guidance.</p>
                    </div>
                </div>
                <div class="text-end text-muted small">
                    <i class="bi bi-calendar-event me-1"></i> {{ now()->format('l, M d, Y') }}
                </div>
            </div>

            @if($situationDoas && count($situationDoas) > 0)
                <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                    <div class="card-header border-0 py-4 px-5 text-center" style="background: linear-gradient(135deg, #0f0f2d 0%, #1a1a3c 100%); cursor: pointer;" onclick="window.location.reload()" title="Refresh Doa">
                        <img src="https://cdn-icons-png.flaticon.com/512/4358/4358686.png" alt="Bismillah" style="width: 80px; filter: invert(1); opacity: 0.8;" class="mb-3">
                        <h5 class="text-white-50 mb-0 text-uppercase letter-spacing-2" id="doa-title">{{ $situationDoas[0]['title'] }}</h5>
                    </div>

                    <!-- Situation Selector -->
                    <div class="bg-light py-3 px-3 text-center border-bottom">
                         <div class="d-flex justify-content-center flex-wrap align-items-center gap-3">
                             <div class="d-flex align-items-center flex-wrap justify-content-center">
                                 <span class="text-muted small me-2 uppercase fw-bold">Situation:</span>
                                 <a href="{{ route('student.doa.situation', ['situation' => 'study']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ (session('doaSituation') && session('doaSituation')['situation'] == 'Study') || (!session('doaSituation') && in_array($situationDoas[0]['title'], ['Doa Before Studying 1'])) ? 'active' : '' }}">📚 Studying</a>
                                 <a href="{{ route('student.doa.situation', ['situation' => 'exam']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ (session('doaSituation') && session('doaSituation')['situation'] == 'Exam') || (!session('doaSituation') && in_array($situationDoas[0]['title'], ['Doa For Exams & Clarity 1'])) ? 'active' : '' }}">📝 Exam</a>
                                 <a href="{{ route('student.doa.situation', ['situation' => 'memory']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ (session('doaSituation') && session('doaSituation')['situation'] == 'Memory') || (!session('doaSituation') && in_array($situationDoas[0]['title'], ['Doa For Memory Retention 1'])) ? 'active' : '' }}">🧠 Memory</a>
                                 <a href="{{ route('student.doa.situation', ['situation' => 'anxious']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ (session('doaSituation') && session('doaSituation')['situation'] == 'Anxious') || (!session('doaSituation') && in_array($situationDoas[0]['title'], ['Doa When Feeling Anxious 1'])) ? 'active' : '' }}">😰 Anxious</a>
                                 <a href="{{ route('student.doa.situation', ['situation' => 'unmotivated']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ (session('doaSituation') && session('doaSituation')['situation'] == 'Unmotivated') || (!session('doaSituation') && in_array($situationDoas[0]['title'], ['Doa For Motivation 1'])) ? 'active' : '' }}">😐 Unmotivated</a>
                             </div>
                         </div>
                    </div>

                    <div class="card-body p-5 text-center position-relative">
                        <!-- Navigation Slider -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <button class="btn btn-outline-secondary rounded-circle" id="prevDoaBtn" style="width: 40px; height: 40px;"><i class="bi bi-chevron-left"></i></button>
                            <span class="text-muted small fw-bold" id="doaCounter">1 / {{ count($situationDoas) }}</span>
                            <button class="btn btn-outline-secondary rounded-circle" id="nextDoaBtn" style="width: 40px; height: 40px;"><i class="bi bi-chevron-right"></i></button>
                        </div>

                        <!-- Arabic Text -->
                        <div class="mb-5 pt-3">
                            <h2 class="display-6 quran-font mb-4" style="line-height: 2.2; color: #0f0f2d; font-family: 'Amiri', serif;" dir="rtl" id="doa-arabic">
                                {{ $situationDoas[0]['arabic'] }}
                            </h2>
                        </div>

                        <hr class="w-25 mx-auto opacity-25 mb-5">

                        <!-- English Translation -->
                        <div class="mb-4">
                            <h5 class="text-uppercase text-muted super-small letter-spacing-2 mb-2">English</h5>
                            <p class="lead fs-4 fst-italic text-dark opacity-75" style="line-height: 1.8;" id="doa-english">
                                "{{ $situationDoas[0]['english'] }}"
                            </p>
                        </div>
                        
                        <!-- Malay Translation -->
                        <div class="mb-5">
                            <h5 class="text-uppercase text-muted super-small letter-spacing-2 mb-2">Bahasa Melayu</h5>
                            <p class="lead fs-4 fst-italic text-dark opacity-75" style="line-height: 1.8;" id="doa-malay">
                                "{{ $situationDoas[0]['malay'] }}"
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning text-center p-5 shadow-sm border-0" style="border-radius: 12px;">
                    <i class="bi bi-wifi-off display-1 mb-3 text-warning opacity-50"></i>
                    <h4 class="fw-bold">Unable to load Daily Doa</h4>
                    <p class="text-muted">Please check your internet connection and try again later.</p>
                    <button onclick="window.location.reload()" class="btn btn-outline-warning mt-3">Try Again</button>
                </div>
            @endif
    </div>
</div>

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&display=swap" rel="stylesheet">
<style>
    .quran-font {
        font-family: 'Amiri', serif;
    }
    .letter-spacing-2 {
        letter-spacing: 2px;
    }
    .super-small {
        font-size: 0.7rem;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const doas = @json($situationDoas ?? []);
        if (doas.length === 0) return;

        let currentIndex = 0;
        
        const titleEl = document.getElementById('doa-title');
        const arabicEl = document.getElementById('doa-arabic');
        const englishEl = document.getElementById('doa-english');
        const malayEl = document.getElementById('doa-malay');
        const counterEl = document.getElementById('doaCounter');
        
        const prevBtn = document.getElementById('prevDoaBtn');
        const nextBtn = document.getElementById('nextDoaBtn');

        function updateDoaView() {
            const doa = doas[currentIndex];
            titleEl.textContent = doa.title;
            arabicEl.textContent = doa.arabic;
            englishEl.textContent = '"' + doa.english + '"';
            malayEl.textContent = '"' + doa.malay + '"';
            counterEl.textContent = `${currentIndex + 1} / ${doas.length}`;
            
            prevBtn.disabled = (currentIndex === 0);
            nextBtn.disabled = (currentIndex === doas.length - 1);
        }

        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateDoaView();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentIndex < doas.length - 1) {
                currentIndex++;
                updateDoaView();
            }
        });

        // Initialize view state
        updateDoaView();
    });
</script>
@endpush
@endsection
