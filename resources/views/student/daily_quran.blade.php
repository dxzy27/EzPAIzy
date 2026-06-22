@extends('layouts.dashboard')

@section('content')
<div class="container-fluid px-4 py-5" style="min-height: 100vh; background-color: #f8f9fa;">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h1 class="display-5 fw-bold text-dark mb-0">Daily Quran</h1>
                    <p class="text-muted lead">Your daily dose of wisdom and guidance.</p>
                </div>
                <div class="text-end text-muted small">
                    <i class="bi bi-calendar-event me-1"></i> {{ now()->format('l, M d, Y') }}
                </div>
            </div>

            @if($dailyAyah)
                <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
                    <div class="card-header border-0 py-4 px-5 text-center" style="background: linear-gradient(135deg, #0f0f2d 0%, #1a1a3c 100%); cursor: pointer;" onclick="window.location.reload()" title="Reset to Daily Ayah">
                        <img src="https://cdn-icons-png.flaticon.com/512/4358/4358686.png" alt="Bismillah" style="width: 80px; filter: invert(1); opacity: 0.8;" class="mb-3">
                        <h5 class="text-white-50 mb-0 text-uppercase letter-spacing-2" id="card-title">Ayah of the Day</h5>
                    </div>

                    <!-- Mood & Mode Selector -->
                    <div class="bg-light py-3 px-3 text-center border-bottom">
                         <div class="d-flex justify-content-center flex-wrap align-items-center gap-3">
                             <div class="d-flex align-items-center flex-wrap justify-content-center">
                                 <span class="text-muted small me-2 uppercase fw-bold">Mood:</span>
                                 <button class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 mood-btn" data-mood="happy">😊 Happy</button>
                                 <button class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 mood-btn" data-mood="sad">😔 Sad</button>
                                 <button class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 mood-btn" data-mood="anxious">😰 Anxious</button>
                                 <button class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 mood-btn" data-mood="unmotivated">😐 Unmotivated</button>
                                 <button class="btn btn-sm btn-outline-primary rounded-pill px-3 m-1 mood-btn" data-mood="lost">🧭 Lost</button>
                             </div>
                             
                             <div class="vr mx-2 d-none d-md-block" style="height: 20px;"></div>
                             
                             <div class="d-flex align-items-center">
                                 <span class="text-muted small me-2 uppercase fw-bold">Mode:</span>
                                 <div class="btn-group" role="group">
                                     <button type="button" class="btn btn-sm btn-outline-dark mode-btn memorize-active-btn" data-mode="normal">Normal</button>
                                     <button type="button" class="btn btn-sm btn-outline-dark mode-btn" data-mode="memorize">Memorize</button>
                                 </div>
                             </div>
                         </div>
                    </div>

                    <div class="card-body p-5 text-center position-relative">
                        <!-- Arabic Text -->
                        <div class="mb-5 pt-3">
                            <h2 class="display-6 quran-font mb-4" style="line-height: 2.2; color: #0f0f2d; font-family: 'Amiri', serif;" dir="rtl" id="verse-arabic">
                                {{ $dailyAyah['arabic']['text'] }}
                            </h2>
                            <span class="badge rounded-pill bg-light text-muted border px-3 py-2" id="verse-ref">
                                {{ $dailyAyah['surah']['englishName'] }} ({{ $dailyAyah['surah']['name'] }}) &bull; Ayah {{ $dailyAyah['numberInSurah'] }}
                            </span>
                        </div>

                        <hr class="w-25 mx-auto opacity-25 mb-5">

                        <!-- English Translation -->
                        <div class="mb-4">
                            <h5 class="text-uppercase text-muted super-small letter-spacing-2 mb-2">English</h5>
                            <p class="lead fs-4 fst-italic text-dark opacity-75" style="line-height: 1.8;" id="verse-en">
                                "{{ $dailyAyah['english']['text'] }}"
                            </p>
                        </div>
                        
                        <!-- Malay Translation -->
                        <div class="mb-5">
                            <h5 class="text-uppercase text-muted super-small letter-spacing-2 mb-2">Bahasa Melayu</h5>
                            <p class="lead fs-4 fst-italic text-dark opacity-75" style="line-height: 1.8;" id="verse-ms">
                                "{{ $dailyAyah['malay']['text'] }}"
                            </p>
                        </div>

                        <!-- Audio Player -->
                        @if(isset($dailyAyah['audio']['audio']))
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-pill p-1 pe-4 shadow-sm border mt-2">
                            <button class="btn btn-primary rounded-circle" id="playBtn" style="width: 50px; height: 50px;">
                                <i class="bi bi-play-fill fs-3 ps-1"></i>
                            </button>
                            <div class="ms-3 d-flex flex-column align-items-start">
                                <span class="small fw-bold text-dark">Listen to Recitation</span>
                                <span class="super-small text-muted">Mishary Rashid Alafasy</span>
                            </div>
                            <audio id="audioPlayer" src="{{ $dailyAyah['audio']['audio'] }}"></audio>
                        </div>
                        @endif

                    </div>
                    
                    <!-- Footer Info -->
                    <div class="card-footer bg-light border-0 py-3 text-center text-muted small">
                        Source: Al-Quran Cloud API
                    </div>
                </div>

                <!-- Reflection Section -->


            @else
                <div class="alert alert-warning text-center p-5 shadow-sm border-0" style="border-radius: 12px;">
                    <i class="bi bi-wifi-off display-1 mb-3 text-warning opacity-50"></i>
                    <h4 class="fw-bold">Unable to load Daily Quran</h4>
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
    
    /* Memorize Mode Styles */
    .memorize-container {
        display: none;
        transition: all 0.3s ease;
    }
    .memorize-mode .full-view {
        display: none;
    }
    .memorize-mode .memorize-container {
        display: block;
    }
    .memorize-chunk {
        font-size: 4rem;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .memorize-active-btn {
        background-color: #0f0f2d !important;
        color: white !important;
        border-color: #0f0f2d !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const audio = document.getElementById('audioPlayer');
        const playBtn = document.getElementById('playBtn');
        const icon = playBtn ? playBtn.querySelector('i') : null;
        const quranContainer = document.querySelector('.card-body');
        
        let words = [];
        let currentChunkIndex = 0;
        const chunkSize = 3; // Number of words per chunk

        if(playBtn && audio) {
            playBtn.addEventListener('click', function() {
                if (audio.paused) {
                    audio.play();
                    icon.classList.remove('bi-play-fill');
                    icon.classList.add('bi-pause-fill');
                } else {
                    audio.pause();
                    icon.classList.remove('bi-pause-fill');
                    icon.classList.add('bi-play-fill');
                }
            });

            audio.addEventListener('ended', function() {
                icon.classList.remove('bi-pause-fill');
                icon.classList.add('bi-play-fill');
            });
        }

        // Initialize Memorize Elements
        // Initialize Memorize Elements
        if (!document.getElementById('memorize-container-top')) {
            // Container for Text Chunk (Above Audio)
            const memDivTop = document.createElement('div');
            memDivTop.id = 'memorize-container-top';
            memDivTop.className = 'memorize-part py-4';
            memDivTop.style.display = 'none';
            memDivTop.innerHTML = `
                <div class="mb-2 position-relative mx-auto" style="max-width: 800px; min-height: 150px; cursor: pointer;" id="mem-card-area">
                    <!-- Overlay (The Light Box) -->
                    <div id="mem-overlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white border rounded shadow-sm d-flex align-items-center justify-content-center" style="z-index: 10; transition: opacity 0.3s ease;">
                         <div class="text-center text-muted">
                             <i class="bi bi-eye-slash fs-1 mb-2"></i>
                             <p class="mb-0 small">Tap to Reveal</p>
                         </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="d-flex align-items-center justify-content-center h-100 w-100 py-4">
                        <h2 class="quran-font memorize-chunk text-dark mb-0" dir="rtl" id="mem-chunk-display" style="font-size: 3.5rem;"></h2>
                    </div>
                </div>
                <p class="lead fst-italic text-muted mt-3" id="mem-translation-display"></p>
            `;
            
            // Container for Controls (Below Audio)
            const memDivBottom = document.createElement('div');
            memDivBottom.id = 'memorize-container-bottom';
            memDivBottom.className = 'memorize-part mt-4';
            memDivBottom.style.display = 'none';
            memDivBottom.innerHTML = `
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-secondary px-4" id="mem-prev" disabled><i class="bi bi-arrow-left"></i> Previous</button>
                    <span class="align-self-center text-muted small" id="mem-progress">1 / 1</span>
                    <button class="btn btn-primary px-4" id="mem-next">Next <i class="bi bi-arrow-right"></i></button>
                </div>
            `;
            
            // Insert Logic
            const audioContainer = document.querySelector('.card-body .d-inline-flex');
            
            if (audioContainer) {
                audioContainer.parentNode.insertBefore(memDivTop, audioContainer);
                audioContainer.parentNode.insertBefore(memDivBottom, audioContainer.nextSibling);
            } else {
                quranContainer.appendChild(memDivTop);
                quranContainer.appendChild(memDivBottom);
            }
            
            // Click to Reveal Logic
            const memCardArea = document.getElementById('mem-card-area');
            const memOverlay = document.getElementById('mem-overlay');
            
            // Clean Click Handler (Toggle Visibility)
            memCardArea.onclick = function() {
                // If overlay is hidden (text visible) -> Show it
                if (memOverlay.classList.contains('d-none')) {
                    memOverlay.classList.remove('d-none');
                    memOverlay.classList.add('d-flex');
                    memOverlay.style.opacity = '1'; 
                } else {
                    // If overlay is visible (text hidden) -> Hide it
                    memOverlay.classList.remove('d-flex');
                    memOverlay.classList.add('d-none');
                    memOverlay.style.opacity = '0';
                }
            };
        }

        const memDisplay = document.getElementById('mem-chunk-display');
        const memTransDisplay = document.getElementById('mem-translation-display');
        const btnPrev = document.getElementById('mem-prev');
        const btnNext = document.getElementById('mem-next');
        const progressDisplay = document.getElementById('mem-progress');
        const memOverlay = document.getElementById('mem-overlay');

        function updateMemorizeView() {
            const start = currentChunkIndex * chunkSize;
            const chunk = words.slice(start, start + chunkSize).join(' ');
            
            memDisplay.innerText = chunk;
            
            // Reset Overlay to VISIBLE (Hidden text)
            // Ensure we reset both class and opacity
            if(memOverlay) {
                memOverlay.classList.remove('d-none');
                memOverlay.classList.add('d-flex');
                memOverlay.style.opacity = '1';
            }
            
            // Full Malay Trans
            const msText = document.getElementById('verse-ms') ? document.getElementById('verse-ms').innerText : '';
            memTransDisplay.innerText = msText;
            
            const totalChunks = Math.ceil(words.length / chunkSize);
            progressDisplay.innerText = `${currentChunkIndex + 1} / ${totalChunks}`;
            
            if(btnPrev) btnPrev.disabled = currentChunkIndex === 0;
            if(btnNext) {
                btnNext.disabled = currentChunkIndex >= totalChunks - 1;
            
                if (currentChunkIndex >= totalChunks - 1) {
                    btnNext.innerHTML = 'Finish <i class="bi bi-check"></i>';
                    btnNext.classList.remove('btn-primary');
                    btnNext.classList.add('btn-success');
                } else {
                    btnNext.innerHTML = 'Next <i class="bi bi-arrow-right"></i>';
                    btnNext.classList.add('btn-primary');
                    btnNext.classList.remove('btn-success');
                }
            }
        }
        
        // Navigation Logic
        if(btnPrev) {
             btnPrev.addEventListener('click', () => {
                if (currentChunkIndex > 0) {
                    currentChunkIndex--;
                    updateMemorizeView();
                }
            });
        }

        if(btnNext) {
             btnNext.addEventListener('click', () => {
                const totalChunks = Math.ceil(words.length / chunkSize);
                if (currentChunkIndex < totalChunks - 1) {
                    currentChunkIndex++;
                    updateMemorizeView();
                } else {
                     // Reset
                    currentChunkIndex = 0;
                    updateMemorizeView();
                }
            });
        }

        // Mode Switch Logic
        const modeBtns = document.querySelectorAll('.mode-btn');
        // Select elements to hide: Arabic Title, Arabic Text, En Title, En Text, Ms Title, Ms Text
        // We can select by classes or structure.
        // Structure: mb-5 (Arabic), mb-4 (English), mb-5 (Malay). Audio is in a .d-inline-flex (we keep this!).
        // hr, .mb-5, .mb-4
        // Be careful not to hide .memorize-part or .mood-selector
        
        modeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const mode = this.dataset.mode;
                
                // Toggle Button Styles
                modeBtns.forEach(b => b.classList.remove('memorize-active-btn'));
                this.classList.add('memorize-active-btn');
                
                const memParts = document.querySelectorAll('.memorize-part');
                const normalElements = quranContainer.querySelectorAll(':scope > .mb-5, :scope > .mb-4, :scope > hr');
                
                if (mode === 'memorize') {
                   // Prepare text
                   const fullText = document.getElementById('verse-arabic').innerText;
                   words = fullText.trim().split(/\s+/);
                   currentChunkIndex = 0;
                   
                   // Hide Normal Elements
                   normalElements.forEach(el => el.style.display = 'none');
                   
                   // Show Memorize Elements
                   memParts.forEach(el => el.style.display = 'block');
                   
                   updateMemorizeView();

                } else {
                   // Show Normal Elements
                   normalElements.forEach(el => el.style.display = 'block');
                   
                   // Hide Memorize Elements
                   memParts.forEach(el => el.style.display = 'none');
                }
            });
        });


        // Mood Logic (Preserved)
        const moodBtns = document.querySelectorAll('.mood-btn');
        moodBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const mood = this.dataset.mood;
                
                // Active Styling
                moodBtns.forEach(b => { 
                    b.classList.remove('btn-primary', 'text-white'); 
                    b.classList.add('btn-outline-primary'); 
                });
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary', 'text-white');

                // Change Title
                document.getElementById('card-title').innerText = "Verse for " + mood.charAt(0).toUpperCase() + mood.slice(1);

                // Fetch
                fetch(`{{ route('student.quran.mood') }}?mood=${mood}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                           document.getElementById('verse-arabic').innerText = data.arabic;
                           document.getElementById('verse-en').innerText = data.translation_en;
                           document.getElementById('verse-ms').innerText = data.translation_ms;
                           document.getElementById('verse-ref').innerText = `${data.surah} • Ayah ${data.numberInSurah}`;
                           
                           if(audio) {
                               audio.src = data.audio;
                               audio.load();
                               if(icon) {
                                   icon.classList.remove('bi-pause-fill');
                                   icon.classList.add('bi-play-fill');
                               }
                           }
                           
                           // If in memorize mode, reset?
                           // Ideally, yes:
                           const activeMode = document.querySelector('.mode-btn.memorize-active-btn').dataset.mode;
                           if(activeMode === 'memorize') {
                                words = data.arabic.trim().split(/\s+/);
                                currentChunkIndex = 0;
                                updateMemorizeView();
                           }
                        }
                    });
            });
        });
    });
</script>
@endpush
@endsection
