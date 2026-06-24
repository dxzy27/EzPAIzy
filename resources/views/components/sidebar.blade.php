<div class="Sidebar">

    {{-- ── Brand ──────────────────────────────────────── --}}
    <div class="sidebar-brand">
        <span class="sidebar-brand-text">Ez<span class="brand-accent">PAI</span>zy</span>
        <button class="sidebar-toggle-mascot" id="mascotToggle" title="Collapse sidebar" aria-label="Toggle sidebar">
            <img src="{{ asset('images/logo.png') }}" alt="Toggle sidebar">
        </button>
    </div>

    {{-- ── Navigation ──────────────────────────────────── --}}
    <ul class="sidebar-nav nav nav-pills flex-column mb-auto list-unstyled">

        {{-- Home --}}
        <li>
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : (auth()->user()->role === 'teacher' ? route('teacher.dashboard') : route('student.dashboard')) }}"
               class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}"
               data-tooltip="Dashboard">
                <i class="bi bi-house-door nav-icon"></i>
                <span class="nav-label">Dashboard</span>
            </a>
        </li>

        @if(auth()->user()->role === 'admin')

            {{-- ── ADMIN LINKS ────────────────────────── --}}
            
            {{-- User Management --}}
            <li>
                <a href="{{ route('admin.users.index') }}"
                   class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                   data-tooltip="Users">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-label">Users</span>
                </a>
            </li>

            {{-- Moderation --}}
            <li>
                <a href="{{ route('admin.moderation.index') }}"
                   class="nav-link {{ request()->routeIs('admin.moderation.*') ? 'active' : '' }}"
                   data-tooltip="Moderation">
                    <i class="bi bi-shield-fill-check nav-icon"></i>
                    <span class="nav-label">Moderation</span>
                </a>
            </li>

        @elseif(auth()->user()->role === 'teacher')

            {{-- ── TEACHER LINKS ──────────────────────── --}}

            {{-- Contents --}}
            <li>
                <a href="#contentsSubmenu" data-bs-toggle="collapse"
                   class="nav-link"
                   role="button"
                   data-tooltip="Contents"
                   aria-expanded="{{ request()->routeIs('teacher.contents.*') || request()->routeIs('teacher.flashcard-sets.*') ? 'true' : 'false' }}">
                    <i class="bi bi-collection nav-icon"></i>
                    <span class="nav-label">Contents</span>
                    <i class="bi bi-chevron-down nav-chevron"></i>
                </a>
                <div class="collapse {{ request()->routeIs('teacher.contents.*') || request()->routeIs('teacher.flashcard-sets.*') ? 'show' : '' }}"
                     id="contentsSubmenu">
                    <ul class="submenu-list">
                        <li>
                            <a href="{{ route('teacher.flashcard-sets.index') }}"
                               class="nav-link {{ request()->routeIs('teacher.flashcard-sets.*') ? 'active' : '' }}">
                                <i class="bi bi-card-text nav-icon"></i>
                                <span class="nav-label">Flashcards</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('teacher.contents.index') }}"
                               class="nav-link {{ request()->routeIs('teacher.contents.*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-text nav-icon"></i>
                                <span class="nav-label">Other Materials</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Quizzes --}}
            <li>
                <a href="#quizzesSubmenu" data-bs-toggle="collapse"
                   class="nav-link"
                   role="button"
                   data-tooltip="Quizzes"
                   aria-expanded="{{ request()->routeIs('teacher.quizzes.*') ? 'true' : 'false' }}">
                    <i class="bi bi-question-circle nav-icon"></i>
                    <span class="nav-label">Quizzes</span>
                    <i class="bi bi-chevron-down nav-chevron"></i>
                </a>
                <div class="collapse {{ request()->routeIs('teacher.quizzes.*') ? 'show' : '' }}"
                     id="quizzesSubmenu">
                    <ul class="submenu-list">
                        <li>
                            <a href="{{ route('teacher.quizzes.index') }}"
                               class="nav-link {{ request()->routeIs('teacher.quizzes.index') ? 'active' : '' }}">
                                <i class="bi bi-clipboard-check nav-icon"></i>
                                <span class="nav-label">Manage Quizzes</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('teacher.quizzes.generate') }}"
                               class="nav-link {{ request()->routeIs('teacher.quizzes.generate') ? 'active' : '' }}">
                                <i class="bi bi-stars nav-icon"></i>
                                <span class="nav-label">AI Generate</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Students --}}
            <li>
                <a href="{{ route('teacher.students.index') }}"
                   class="nav-link {{ request()->routeIs('teacher.students.*') ? 'active' : '' }}"
                   data-tooltip="Students">
                    <i class="bi bi-people nav-icon"></i>
                    <span class="nav-label">Students</span>
                </a>
            </li>

        @else

            {{-- ── STUDENT LINKS ──────────────────────── --}}

            {{-- Materials --}}
            <li>
                <a href="#materialsSubmenu" data-bs-toggle="collapse"
                   class="nav-link"
                   role="button"
                   data-tooltip="Materials"
                   aria-expanded="{{ request()->routeIs('student.contents.*') || request()->routeIs('student.flashcards.*') ? 'true' : 'false' }}">
                    <i class="bi bi-collection nav-icon"></i>
                    <span class="nav-label">Materials</span>
                    <i class="bi bi-chevron-down nav-chevron"></i>
                </a>
                <div class="collapse {{ request()->routeIs('student.contents.*') || request()->routeIs('student.flashcards.*') ? 'show' : '' }}"
                     id="materialsSubmenu">
                    <ul class="submenu-list">
                        <li>
                            <a href="{{ route('student.flashcards.index') }}"
                               class="nav-link {{ request()->routeIs('student.flashcards.*') ? 'active' : '' }}">
                                <i class="bi bi-card-text nav-icon"></i>
                                <span class="nav-label">Flashcards</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('student.contents.index') }}"
                               class="nav-link {{ request()->routeIs('student.contents.*') ? 'active' : '' }}">
                                <i class="bi bi-journal-text nav-icon"></i>
                                <span class="nav-label">Other Materials</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Quizzes --}}
            <li>
                <a href="{{ route('student.quizzes') }}"
                   class="nav-link {{ request()->routeIs('student.quizzes') ? 'active' : '' }}"
                   data-tooltip="Quizzes">
                    <i class="bi bi-pencil-square nav-icon"></i>
                    <span class="nav-label">Quizzes</span>
                </a>
            </li>

            {{-- Revision --}}
            <li>
                <a href="{{ route('student.revision') }}"
                   class="nav-link {{ request()->routeIs('student.revision') ? 'active' : '' }}"
                   data-tooltip="Revision">
                    <i class="bi bi-star nav-icon"></i>
                    <span class="nav-label">Revision</span>
                </a>
            </li>

            {{-- Progress --}}
            <li>
                <a href="{{ route('student.progress') }}"
                   class="nav-link {{ request()->routeIs('student.progress') ? 'active' : '' }}"
                   data-tooltip="My Progress">
                    <i class="bi bi-bar-chart-line nav-icon"></i>
                    <span class="nav-label">My Progress</span>
                </a>
            </li>

            {{-- Daily Quran --}}
            <li>
                <a href="{{ route('student.daily_quran') }}"
                   class="nav-link {{ request()->routeIs('student.daily_quran') ? 'active' : '' }}"
                   data-tooltip="Daily Quran">
                    <i class="bi bi-moon-stars nav-icon"></i>
                    <span class="nav-label">Daily Quran</span>
                </a>
            </li>

            {{-- Diagnosis --}}
            <li>
                <a href="{{ route('student.diagnosis.show') }}"
                   class="nav-link {{ request()->routeIs('student.diagnosis.*') ? 'active' : '' }}"
                   data-tooltip="Diagnosis">
                    <i class="bi bi-clipboard-pulse nav-icon"></i>
                    <span class="nav-label">Diagnosis</span>
                </a>
            </li>

            {{-- My Folders (Read/Write Learners Only) --}}
            @if(auth()->user()->learning_style === 'read_write')
                @php
                    $studentNoteTopics = \App\Models\StudentNote::where('user_id', auth()->id())
                        ->select('topic')
                        ->distinct()
                        ->orderBy('topic')
                        ->pluck('topic');
                @endphp
                <li>
                    <a href="#foldersSubmenu" data-bs-toggle="collapse"
                       class="nav-link {{ request()->routeIs('student.notes.*') ? 'active' : '' }}"
                       role="button"
                       data-tooltip="My Folders"
                       aria-expanded="{{ request()->routeIs('student.notes.*') ? 'true' : 'false' }}">
                        <i class="bi bi-folder2 nav-icon"></i>
                        <span class="nav-label">My Folders</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('student.notes.*') ? 'show' : '' }}" id="foldersSubmenu">
                        <ul class="submenu-list" style="padding-left: 1.5rem; list-style: none;">
                            @forelse($studentNoteTopics as $folderTopic)
                                <li>
                                    <a href="{{ route('student.notes.folder', $folderTopic) }}"
                                       class="nav-link py-1 {{ request()->routeIs('student.notes.folder') && request()->route('topic') === $folderTopic ? 'active' : '' }}"
                                       style="font-size: 0.88rem;">
                                        <i class="bi bi-folder-fill nav-icon text-warning me-1"></i>
                                        <span class="nav-label">{{ $folderTopic }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-muted small px-3 py-1">No folders saved yet</li>
                            @endforelse
                        </ul>
                    </div>
                </li>
            @endif

        @endif
    </ul>

    {{-- ── Footer / User Info ──────────────────────────── --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user-role">{{ auth()->user()->role }}</div>
            </div>
        </div>
    </div>

</div>
