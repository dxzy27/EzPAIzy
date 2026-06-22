@extends('layouts.dashboard')

@section('content')

@php $isAuditory = auth()->user()?->learning_style === 'auditory'; @endphp

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>{{ $content->title }}</h1>
            <p class="text-muted">Created: {{ $content->created_at->format('M d, Y H:i') }}</p>

            {{-- ── Auditory Mode Badge ── --}}
            @if($isAuditory)
            <span class="badge d-inline-flex align-items-center gap-1"
                  style="background:#e0f2fe;color:#0c4a6e;font-size:.78rem;font-weight:700;border-radius:20px;padding:5px 12px;">
                <i class="bi bi-ear-fill"></i> Auditory Mode — TTS active
            </span>
            @endif
        </div>
        <div class="col-md-4 text-end">
            @if(auth()->user()->role === 'teacher')
                <a href="{{ route('teacher.contents.edit', $content) }}" class="btn btn-warning">Edit</a>
                <form action="{{ route('teacher.contents.destroy', $content) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Content</h5>
                </div>
                <div class="card-body">
                    @if($content->file_path)
                        <div class="mb-4 text-center">
                            @if(in_array(strtolower($content->file_type), ['jpg', 'jpeg', 'png', 'gif']))
                                <img src="{{ asset('storage/' . $content->file_path) }}" class="img-fluid rounded border" alt="Attachment" style="max-height: 500px;">
                            @elseif(in_array(strtolower($content->file_type), ['mp4', 'webm', 'ogg']))
                                <video controls class="w-100 rounded border" style="max-height: 500px;">
                                    <source src="{{ asset('storage/' . $content->file_path) }}" type="video/{{ $content->file_type }}">
                                    Your browser does not support the video tag.
                                </video>
                            @else
                                <div class="p-4 bg-light border rounded d-inline-block">
                                    <i class="bi bi-file-earmark-text display-4 text-primary"></i>
                                    <br>
                                    <a href="{{ asset('storage/' . $content->file_path) }}" class="btn btn-primary mt-2" download>
                                        <i class="bi bi-download me-1"></i> Download File ({{ strtoupper($content->file_type) }})
                                    </a>
                                </div>
                            @endif
                        </div>
                        <hr>
                    @endif

                    <label class="fw-bold mb-2">Description</label>
                    <div class="content-body" id="tts-content-body">
                        {!! nl2br(e($content->content)) !!}
                    </div>
                </div>
            </div>

            {{-- ── Say It Back Callout (Auditory only) ── --}}
            @if($isAuditory)
            <div class="mt-4 p-4 d-flex align-items-start gap-3"
                 style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:14px;">
                <div style="font-size:1.6rem;flex-shrink:0;line-height:1;">🎙️</div>
                <div>
                    <div class="fw-bold mb-1" style="color:#0c4a6e;font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;">
                        Auditory Retention Tip
                    </div>
                    <div style="color:#075985;font-size:.92rem;line-height:1.55;">
                        Now close this page and explain out loud — <em>in your own words</em> — what you just read.
                        If you can say it clearly without looking, you've truly encoded it. This technique boosts retention by up to 50%.
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            @if(auth()->user()->role === 'teacher')
                <a href="{{ route('teacher.contents.index') }}" class="btn btn-secondary">Back to Contents</a>
            @else
                <a href="{{ route('student.contents.index') }}" class="btn btn-secondary">Back to Contents</a>
            @endif
        </div>
    </div>
</div>

{{-- ── TTS Floating Audio Bar (Auditory students only) ── --}}
@if($isAuditory)
<div id="tts-bar"
     style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
            background:#0c4a6e;color:#fff;border-radius:16px;
            padding:12px 20px;display:flex;align-items:center;gap:14px;
            box-shadow:0 8px 32px rgba(0,0,0,.25);z-index:9999;
            min-width:340px;max-width:520px;
            animation:ttsSlideUp .35s cubic-bezier(.4,0,.2,1);">

    <style>
        @keyframes ttsSlideUp {
            from { opacity:0; transform:translateX(-50%) translateY(20px); }
            to   { opacity:1; transform:translateX(-50%) translateY(0); }
        }
        #tts-bar button {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            color: #fff; border-radius: 9px;
            width:38px; height:38px;
            display:flex; align-items:center; justify-content:center;
            cursor:pointer; transition: background .15s;
            flex-shrink:0; font-size:1rem;
        }
        #tts-bar button:hover { background: rgba(255,255,255,.25); }
        #tts-bar button.active { background: #0ea5e9; border-color:#38bdf8; }
        #tts-speed {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            color: #fff; border-radius: 8px;
            padding: 4px 8px; font-size:.8rem; cursor:pointer;
        }
        #tts-speed option { background:#0c4a6e; }
        #tts-label {
            font-size:.78rem; opacity:.75; white-space:nowrap;
            overflow:hidden; text-overflow:ellipsis; max-width:140px;
        }
        #tts-progress-wrap {
            flex:1; height:4px; background:rgba(255,255,255,.2);
            border-radius:4px; overflow:hidden; min-width:60px;
        }
        #tts-progress { height:100%; background:#38bdf8; width:0%; transition:width .3s; border-radius:4px; }
    </style>

    {{-- Icon --}}
    <i class="bi bi-soundwave" style="font-size:1.2rem;flex-shrink:0;opacity:.8;"></i>

    {{-- Label --}}
    <span id="tts-label">🔊 Read Aloud</span>

    {{-- Progress bar --}}
    <div id="tts-progress-wrap"><div id="tts-progress"></div></div>

    {{-- Controls --}}
    <button id="tts-play-btn" title="Play" onclick="ttsPlay()"><i class="bi bi-play-fill"></i></button>
    <button id="tts-pause-btn" title="Pause" onclick="ttsPause()" style="display:none;"><i class="bi bi-pause-fill"></i></button>
    <button id="tts-stop-btn" title="Stop" onclick="ttsStop()"><i class="bi bi-stop-fill"></i></button>

    {{-- Speed --}}
    <select id="tts-speed" onchange="ttsChangeSpeed(this.value)" title="Speed">
        <option value="0.75">0.75×</option>
        <option value="1" selected>1×</option>
        <option value="1.25">1.25×</option>
        <option value="1.5">1.5×</option>
    </select>

    {{-- Dismiss --}}
    <button onclick="document.getElementById('tts-bar').remove()" title="Dismiss" style="opacity:.6;">
        <i class="bi bi-x-lg" style="font-size:.85rem;"></i>
    </button>
</div>

<script>
(function() {
    const synth = window.speechSynthesis;
    let utterance = null;
    let currentRate = 1;
    let charIndex  = 0;
    let totalChars = 1;

    // Extract plain text from content body
    const body = document.getElementById('tts-content-body');
    const rawText = body ? body.innerText.trim() : '';
    totalChars = rawText.length || 1;

    const playBtn  = document.getElementById('tts-play-btn');
    const pauseBtn = document.getElementById('tts-pause-btn');
    const label    = document.getElementById('tts-label');
    const progress = document.getElementById('tts-progress');

    function buildUtterance(text, startChar = 0) {
        const u = new SpeechSynthesisUtterance(text);
        u.rate = currentRate;
        u.lang = 'en-US';

        u.onboundary = function(e) {
            if (e.name === 'word') {
                charIndex = startChar + e.charIndex;
                const pct = Math.min(100, Math.round((charIndex / totalChars) * 100));
                progress.style.width = pct + '%';
                label.textContent = '🔊 Reading… ' + pct + '%';
            }
        };
        u.onend = function() {
            playBtn.style.display  = '';
            pauseBtn.style.display = 'none';
            progress.style.width   = '100%';
            label.textContent      = '✅ Done';
            charIndex = 0;
        };
        u.onerror = function(e) {
            if (e.error !== 'interrupted') console.warn('TTS error:', e.error);
        };
        return u;
    }

    window.ttsPlay = function() {
        synth.cancel();
        const resumeText = charIndex > 0 ? rawText.slice(charIndex) : rawText;
        utterance = buildUtterance(resumeText, charIndex);
        synth.speak(utterance);
        playBtn.style.display  = 'none';
        pauseBtn.style.display = '';
        label.textContent      = '🔊 Reading…';
    };

    window.ttsPause = function() {
        if (synth.speaking && !synth.paused) {
            synth.pause();
            pauseBtn.style.display = 'none';
            playBtn.style.display  = '';
            label.textContent      = '⏸ Paused';
        } else if (synth.paused) {
            synth.resume();
            pauseBtn.style.display = '';
            playBtn.style.display  = 'none';
            label.textContent      = '🔊 Reading…';
        }
    };

    window.ttsStop = function() {
        synth.cancel();
        charIndex = 0;
        progress.style.width   = '0%';
        playBtn.style.display  = '';
        pauseBtn.style.display = 'none';
        label.textContent      = '🔊 Read Aloud';
    };

    window.ttsChangeSpeed = function(val) {
        currentRate = parseFloat(val);
        if (synth.speaking) {
            const resumeChar = charIndex;
            synth.cancel();
            const resumeText = rawText.slice(resumeChar);
            utterance = buildUtterance(resumeText, resumeChar);
            synth.speak(utterance);
        }
    };

    // Stop TTS when navigating away
    window.addEventListener('beforeunload', () => synth.cancel());
})();
</script>
@endif

@endsection

