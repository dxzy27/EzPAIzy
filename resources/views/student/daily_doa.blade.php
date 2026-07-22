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

                    <!-- Situation & Mode Selector -->
                    <div class="bg-light py-3 px-3 text-center border-bottom">
                         <div class="d-flex justify-content-center flex-wrap align-items-center gap-3">
                             <div class="d-flex align-items-center flex-wrap justify-content-center">
                                 <span class="text-muted small me-2 uppercase fw-bold">Situation:</span>
                                 <a href="{{ route('student.daily_doa', ['situation' => 'study']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ $situation == 'study' ? 'active' : '' }}">📚 Studying</a>
                                 <a href="{{ route('student.daily_doa', ['situation' => 'exam']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ $situation == 'exam' ? 'active' : '' }}">📝 Exam</a>
                                 <a href="{{ route('student.daily_doa', ['situation' => 'memory']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ $situation == 'memory' ? 'active' : '' }}">🧠 Memory</a>
                                 <a href="{{ route('student.daily_doa', ['situation' => 'anxious']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ $situation == 'anxious' ? 'active' : '' }}">😰 Anxious</a>
                                 <a href="{{ route('student.daily_doa', ['situation' => 'unmotivated']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 {{ $situation == 'unmotivated' ? 'active' : '' }}">😐 Unmotivated</a>
                             </div>

                             <div class="d-flex align-items-center flex-wrap justify-content-center border-start ps-3 ms-3">
                                 <span class="text-muted small me-2 uppercase fw-bold">Mode:</span>
                                 <button class="btn btn-sm btn-outline-dark rounded-pill px-3 m-1 mode-btn memorize-active-btn" data-mode="normal">Normal</button>
                                 <button class="btn btn-sm btn-outline-dark rounded-pill px-3 m-1 mode-btn" data-mode="memorize">Memorize</button>
                             </div>
                         </div>
                    </div>

                    <div class="card-body p-5 text-center position-relative" id="doa-container">
                        <!-- Navigation Slider -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <button class="btn btn-outline-secondary rounded-circle" id="prevDoaBtn" style="width: 40px; height: 40px;"><i class="bi bi-chevron-left"></i></button>
                            <span class="text-muted small fw-bold" id="doaCounter">1 / {{ count($situationDoas) }}</span>
                            <button class="btn btn-outline-secondary rounded-circle" id="nextDoaBtn" style="width: 40px; height: 40px;"><i class="bi bi-chevron-right"></i></button>
                        </div>

                        <!-- Normal Mode Elements -->
                        <!-- Arabic Text -->
                        <div class="mb-5 pt-3 normal-element">
                            <h2 class="display-6 quran-font mb-4" style="line-height: 2.2; color: #0f0f2d; font-family: 'Amiri', serif;" dir="rtl" id="doa-arabic">
                                {{ $situationDoas[0]['arabic'] }}
                            </h2>
                        </div>

                        <hr class="w-25 mx-auto opacity-25 mb-5 normal-element">

                        <!-- English Translation -->
                        <div class="mb-4 normal-element">
                            <h5 class="text-uppercase text-muted super-small letter-spacing-2 mb-2">English</h5>
                            <p class="lead fs-4 fst-italic text-dark opacity-75" style="line-height: 1.8;" id="doa-english">
                                "{{ $situationDoas[0]['english'] }}"
                            </p>
                        </div>
                        
                        <!-- Malay Translation -->
                        <div class="mb-5 normal-element">
                            <h5 class="text-uppercase text-muted super-small letter-spacing-2 mb-2">Bahasa Melayu</h5>
                            <p class="lead fs-4 fst-italic text-dark opacity-75" style="line-height: 1.8;" id="doa-malay">
                                "{{ $situationDoas[0]['malay'] }}"
                            </p>
                        </div>

                        <!-- Audio Button (Always visible) -->
                        <button class="btn d-inline-flex align-items-center gap-3 px-4 py-2 mt-4" id="playBtn" style="border-radius: 50px; background: white; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.3s ease;">
                            <div class="d-flex align-items-center justify-content-center rounded-circle shadow-sm" style="width: 45px; height: 45px; background: #1bbc9b; color: white;">
                                <i class="bi bi-play-fill fs-4" id="playIcon"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">Listen to Doa</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Interactive Reading</div>
                            </div>
                        </button>

                        <!-- Memorize Part (Hidden by Default) -->
                        <div class="memorize-part" style="display: none;">
                            <div class="d-flex justify-content-center align-items-center mb-4 mt-5">
                                <button class="btn btn-primary rounded-circle me-3 shadow-sm" id="mem-prev" style="width:45px; height:45px;" disabled><i class="bi bi-arrow-left"></i></button>
                                <span class="fw-bold text-muted px-3 py-1 bg-light rounded-pill" id="mem-progress">1 / 1</span>
                                <button class="btn btn-primary rounded-circle ms-3 shadow-sm" id="mem-next" style="width:45px; height:45px;"><i class="bi bi-arrow-right"></i></button>
                            </div>
                            
                            <div class="position-relative mx-auto my-5 p-4 rounded-4" id="mem-card-area" style="max-width: 600px; background: rgba(0,0,0,0.02); min-height: 250px; cursor: pointer; border: 2px dashed rgba(0,0,0,0.1);">
                                
                                <!-- Text Chunk to Memorize -->
                                <h2 class="display-5 quran-font text-dark mb-0 d-flex align-items-center justify-content-center" style="line-height: 2; height: 100%; min-height: 200px;" dir="rtl" id="mem-chunk-display">
                                    <!-- JS inserts text -->
                                </h2>
                                
                                <!-- Overlay (Hides text initially) -->
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center rounded-4" id="mem-overlay" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); transition: all 0.3s ease; z-index: 10;">
                                     <i class="bi bi-eye-slash text-muted fs-1 mb-2"></i>
                                     <span class="text-muted fw-bold">Click to reveal</span>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <p class="text-muted fst-italic" id="mem-translation-display"></p>
                            </div>
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
    .memorize-active-btn {
        background-color: #212529 !important;
        color: white !important;
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
        const playBtn = document.getElementById('playBtn');
        const playIcon = document.getElementById('playIcon');

        // Memorize Variables
        let words = [];
        let currentChunkIndex = 0;
        const chunkSize = 2; // Show 2 words at a time
        
        const memCardArea = document.getElementById('mem-card-area');
        const memOverlay = document.getElementById('mem-overlay');
        const memDisplay = document.getElementById('mem-chunk-display');
        const memTransDisplay = document.getElementById('mem-translation-display');
        const memPrevBtn = document.getElementById('mem-prev');
        const memNextBtn = document.getElementById('mem-next');
        const memProgressDisplay = document.getElementById('mem-progress');

        // Text to Speech
        let synth = window.speechSynthesis;
        let isPlaying = false;

        function updateDoaView() {
            const doa = doas[currentIndex];
            titleEl.textContent = doa.title;
            arabicEl.textContent = doa.arabic;
            englishEl.textContent = '"' + doa.english + '"';
            malayEl.textContent = '"' + doa.malay + '"';
            counterEl.textContent = `${currentIndex + 1} / ${doas.length}`;
            
            prevBtn.disabled = (currentIndex === 0);
            nextBtn.disabled = (currentIndex === doas.length - 1);

            // Stop TTS if running when changing Doa
            if (synth.speaking) {
                synth.cancel();
                isPlaying = false;
                playIcon.classList.remove('bi-pause-fill');
                playIcon.classList.add('bi-play-fill');
            }

            // Update memorize view if active
            const activeMode = document.querySelector('.mode-btn.memorize-active-btn');
            if(activeMode && activeMode.dataset.mode === 'memorize') {
                words = doa.arabic.trim().split(/\s+/);
                currentChunkIndex = 0;
                updateMemorizeView();
            }
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

        // TTS Audio Logic (Using Google Translate TTS for reliable Arabic playback)
        let audioPlayer = new Audio();
        let isPlaying = false;

        playBtn.addEventListener('click', () => {
            if (isPlaying) {
                audioPlayer.pause();
                isPlaying = false;
                playIcon.classList.remove('bi-pause-fill');
                playIcon.classList.add('bi-play-fill');
                return;
            }

            const doa = doas[currentIndex];
            const text = encodeURIComponent(doa.arabic);
            
            // Using Google's TTS endpoint
            audioPlayer.src = `https://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=ar&q=${text}`;
            
            audioPlayer.play().then(() => {
                isPlaying = true;
                playIcon.classList.remove('bi-play-fill');
                playIcon.classList.add('bi-pause-fill');
            }).catch(e => {
                console.error("Audio playback failed:", e);
                alert("Failed to play audio. Your browser might be blocking it.");
                isPlaying = false;
                playIcon.classList.remove('bi-pause-fill');
                playIcon.classList.add('bi-play-fill');
            });
        });

        audioPlayer.onended = () => {
            isPlaying = false;
            playIcon.classList.remove('bi-pause-fill');
            playIcon.classList.add('bi-play-fill');
        };

        // Mode Switch Logic
        const modeBtns = document.querySelectorAll('.mode-btn');
        const normalElements = document.querySelectorAll('.normal-element');
        const memorizeParts = document.querySelectorAll('.memorize-part');

        modeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const mode = this.dataset.mode;
                
                modeBtns.forEach(b => b.classList.remove('memorize-active-btn'));
                this.classList.add('memorize-active-btn');
                
                if (mode === 'memorize') {
                   // Setup memorize text
                   const fullText = arabicEl.textContent;
                   words = fullText.trim().split(/\s+/);
                   currentChunkIndex = 0;
                   
                   normalElements.forEach(el => el.style.display = 'none');
                   memorizeParts.forEach(el => el.style.display = 'block');
                   
                   updateMemorizeView();
                } else {
                   normalElements.forEach(el => el.style.display = 'block');
                   memorizeParts.forEach(el => el.style.display = 'none');
                }
            });
        });

        // Memorize Card Reveal Logic
        if(memCardArea && memOverlay) {
            memCardArea.onclick = function() {
                if (memOverlay.classList.contains('d-none')) {
                    memOverlay.classList.remove('d-none');
                    memOverlay.classList.add('d-flex');
                    memOverlay.style.opacity = '1'; 
                } else {
                    memOverlay.classList.remove('d-flex');
                    memOverlay.classList.add('d-none');
                    memOverlay.style.opacity = '0';
                }
            };
        }

        function updateMemorizeView() {
            const start = currentChunkIndex * chunkSize;
            const chunk = words.slice(start, start + chunkSize).join(' ');
            
            memDisplay.innerText = chunk;
            
            // Reset Overlay to VISIBLE
            if(memOverlay) {
                memOverlay.classList.remove('d-none');
                memOverlay.classList.add('d-flex');
                memOverlay.style.opacity = '1';
            }
            
            // Show full malay translation at bottom
            memTransDisplay.innerText = malayEl.textContent;
            
            const totalChunks = Math.ceil(words.length / chunkSize);
            memProgressDisplay.innerText = `${currentChunkIndex + 1} / ${totalChunks}`;
            
            if(memPrevBtn) memPrevBtn.disabled = currentChunkIndex === 0;
            if(memNextBtn) {
                memNextBtn.disabled = currentChunkIndex >= totalChunks - 1;
                
                if (currentChunkIndex >= totalChunks - 1) {
                    memNextBtn.innerHTML = 'Finish <i class="bi bi-check"></i>';
                    memNextBtn.classList.remove('btn-primary');
                    memNextBtn.classList.add('btn-success');
                } else {
                    memNextBtn.innerHTML = 'Next <i class="bi bi-arrow-right"></i>';
                    memNextBtn.classList.add('btn-primary');
                    memNextBtn.classList.remove('btn-success');
                }
            }
        }

        if(memPrevBtn) {
             memPrevBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent card flip
                if (currentChunkIndex > 0) {
                    currentChunkIndex--;
                    updateMemorizeView();
                }
            });
        }

        if(memNextBtn) {
             memNextBtn.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent card flip
                const totalChunks = Math.ceil(words.length / chunkSize);
                if (currentChunkIndex < totalChunks - 1) {
                    currentChunkIndex++;
                    updateMemorizeView();
                } else {
                    currentChunkIndex = 0;
                    updateMemorizeView();
                }
            });
        }

        // Initialize view state
        updateDoaView();
    });
</script>
@endpush
@endsection
