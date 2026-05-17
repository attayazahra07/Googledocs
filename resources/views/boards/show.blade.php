<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $board->title }}
                </h2>
            </div>
            <div class="flex items-center space-x-2">
                <div id="user-badges" class="flex -space-x-2 overflow-hidden mr-2">
                    <!-- Badges of online users will be rendered here dynamically -->
                </div>
                <span class="text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400 px-3 py-1 rounded-full font-semibold">
                    Pemilik: {{ $board->owner->name }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Custom CSS for Premium Micro-Animations -->
    @push('scripts')
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }
        .shake {
            animation: shake 0.4s ease-in-out;
        }
    </style>
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alert Notifications -->
            <div id="alert-container" class="hidden"></div>
            @if (session('success'))
                <div class="bg-emerald-500 text-white p-4 rounded-lg shadow-md mb-6 flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-white hover:text-emerald-200 font-bold">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-500 text-white p-4 rounded-lg shadow-md mb-6 flex justify-between items-center">
                    <span>{{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-white hover:text-rose-200 font-bold">&times;</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-500 text-white p-4 rounded-lg shadow-md mb-6">
                    <div class="flex justify-between items-start mb-2">
                        <span class="font-bold text-sm">Terjadi Kesalahan:</span>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-rose-200 font-bold leading-none">&times;</button>
                    </div>
                    <ul class="list-disc pl-5 text-xs space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Main Task (Habit) Area -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Board Info & Add Habit -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
                        @if($board->description)
                            <p class="text-gray-600 dark:text-gray-400 mb-6 text-sm bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border-l-4 border-indigo-500">
                                {{ $board->description }}
                            </p>
                        @endif

                        <!-- Form Add Habit -->
                        <form id="form-add-habit" action="{{ route('habits.store', $board) }}" method="POST" class="flex space-x-3">
                            @csrf
                            <input type="text" name="title" id="input-habit-title" required placeholder="Tambah kebiasaan baru..." class="flex-grow rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow transition">
                                Tambah
                            </button>
                        </form>
                    </div>

                    <!-- Habit List -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Daftar Kebiasaan
                        </h3>

                        <div id="habit-list" class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($board->habits as $habit)
                                <div id="habit-item-{{ $habit->id }}" class="py-4 flex flex-col justify-center group habit-item" data-id="{{ $habit->id }}" data-updated-at="{{ $habit->updated_at->toISOString() }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <!-- Checkbox Button -->
                                            <button type="button" onclick="toggleHabit({{ $habit->id }})" class="focus:outline-none transition">
                                                <div id="checkbox-{{ $habit->id }}" class="w-6 h-6 rounded-full flex items-center justify-center shadow-md transition-all duration-300 {{ $habit->is_completed ? 'bg-emerald-500 text-white' : 'border-2 border-gray-300 dark:border-gray-600 hover:border-emerald-500' }}">
                                                    @if($habit->is_completed)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                    @endif
                                                </div>
                                            </button>
                                            
                                            <!-- Title -->
                                            <span id="title-{{ $habit->id }}" class="text-sm font-semibold transition-all duration-300 {{ $habit->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-800 dark:text-gray-200' }}">
                                                {{ $habit->title }}
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <!-- Live Activity / Typing Tracker -->
                                            <span id="activity-{{ $habit->id }}" class="hidden text-[10px] text-indigo-500 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-full animate-pulse">
                                                Sedang aktif...
                                            </span>

                                            <!-- Updater Status -->
                                            <span id="updater-{{ $habit->id }}" class="text-[10px] text-gray-400 dark:text-gray-500 italic bg-gray-50 dark:bg-gray-700/30 px-2 py-1 rounded {{ $habit->updater ? '' : 'hidden' }}">
                                                Update: {{ $habit->updater->name ?? '' }}
                                            </span>

                                            <!-- Delete Button -->
                                            <button type="button" onclick="deleteHabit({{ $habit->id }})" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition duration-150">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div id="no-habits-placeholder" class="text-center py-10">
                                    <p class="text-gray-500 dark:text-gray-400 italic">Belum ada tugas atau habit di papan ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Sidebar Area (Collaborators & Version History) -->
                <div class="space-y-6">
                    
                    <!-- Invite Collaborator -->
                    @if($board->owner_id === Auth::id())
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
                            <h3 class="text-md font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                Undang Kolaborator
                            </h3>
                            <form action="{{ route('boards.invite', $board) }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="email" name="email" required placeholder="Masukkan email teman..." class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('email')
                                    <p class="text-xs text-red-500 mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-widest rounded-lg shadow transition">
                                    Kirim Undangan
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Collaborator / Online List -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
                        <h3 class="text-md font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Anggota & Status Online
                        </h3>
                        <div id="collaborator-list" class="space-y-3">
                            <!-- Owner -->
                            <div class="flex items-center justify-between" id="user-item-{{ $board->owner_id }}">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-xs uppercase shadow">
                                        {{ substr($board->owner->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $board->owner->name }}</span>
                                        <span class="text-[9px] text-indigo-500 uppercase tracking-widest font-bold">Pemilik</span>
                                    </div>
                                </div>
                                <span id="online-dot-{{ $board->owner_id }}" class="w-2.5 h-2.5 bg-gray-300 dark:bg-gray-600 rounded-full" title="Offline"></span>
                            </div>

                            <!-- Collaborators -->
                            @foreach($board->collaborators as $collab)
                                <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-2" id="user-item-{{ $collab->id }}">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs uppercase shadow">
                                            {{ substr($collab->name, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $collab->name }}</span>
                                            <span class="text-[9px] text-emerald-500 uppercase tracking-widest font-bold">Kolaborator</span>
                                        </div>
                                    </div>
                                    <span id="online-dot-{{ $collab->id }}" class="w-2.5 h-2.5 bg-gray-300 dark:bg-gray-600 rounded-full" title="Offline"></span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Riwayat Versi / Version History (Snapshot) -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
                        <h3 class="text-md font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Riwayat Aktivitas Papan
                        </h3>
                        <div class="relative pl-4 border-l border-gray-200 dark:border-gray-700 space-y-4 max-h-[300px] overflow-y-auto" id="version-timeline">
                            @forelse($board->versions as $version)
                                <div class="relative timeline-item" id="log-item-{{ $version->id }}">
                                    <!-- Indicator dot -->
                                    <span class="absolute -left-[21px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-800 bg-amber-400 shadow-sm"></span>
                                    <div class="flex flex-col">
                                        <p class="text-xs text-gray-700 dark:text-gray-300 font-semibold">{{ $version->description }}</p>
                                        <span class="text-[9px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $version->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <div id="no-history-placeholder" class="text-center py-6">
                                    <p class="text-xs text-gray-400 italic">Belum ada riwayat aktivitas.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <!-- Real-Time Scripts -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const boardId = {{ $board->id }};
            const currentUserId = {{ Auth::id() }};
            const currentUserName = "{{ Auth::user()->name }}";
            
            const habitList = document.getElementById('habit-list');
            const alertContainer = document.getElementById('alert-container');
            const userBadges = document.getElementById('user-badges');
            const versionTimeline = document.getElementById('version-timeline');
            
            // --- Helper: Dynamic Alerts ---
            function showAlert(message, type = 'success') {
                alertContainer.className = `p-4 rounded-lg shadow-md mb-6 flex justify-between items-center text-white ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'}`;
                alertContainer.innerHTML = `
                    <span>${message}</span>
                    <button onclick="this.parentElement.classList.add('hidden')" class="text-white hover:opacity-75 font-bold">&times;</button>
                `;
                alertContainer.classList.remove('hidden');
                setTimeout(() => alertContainer.classList.add('hidden'), 4000);
            }

            // --- Helper: Prepend Version History Log ---
            function prependVersionLog(log) {
                const placeholder = document.getElementById('no-history-placeholder');
                if (placeholder) placeholder.remove();

                const div = document.createElement('div');
                div.id = `log-item-${log.id}`;
                div.className = 'relative timeline-item transition-all duration-500 opacity-0 transform -translate-y-2';
                div.innerHTML = `
                    <span class="absolute -left-[21px] top-1.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-800 bg-amber-400 shadow-sm"></span>
                    <div class="flex flex-col">
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-semibold">${log.description}</p>
                        <span class="text-[9px] text-gray-400 dark:text-gray-500 mt-0.5">${log.time || 'Baru saja'}</span>
                    </div>
                `;
                
                versionTimeline.insertBefore(div, versionTimeline.firstChild);
                
                setTimeout(() => {
                    div.classList.remove('opacity-0', '-translate-y-2');
                }, 50);
            }

            // --- Form Add Habit Setup (Double-Click Prevention & Spinner) ---
            const formAddHabit = document.getElementById('form-add-habit');
            const inputHabitTitle = document.getElementById('input-habit-title');
            if (formAddHabit) {
                formAddHabit.addEventListener('submit', () => {
                    const submitBtn = formAddHabit.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1rem; height: 1rem; vertical-align: middle; display: inline-block; margin-right: 0.5rem;">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
                                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity: 0.75;"></path>
                            </svg>
                            Menyimpan...
                        `;
                    }
                });
            }

            // --- AJAX: Toggle Checklist (With Conflict Resolution) ---
            window.toggleHabit = function(habitId) {
                const checkbox = document.getElementById(`checkbox-${habitId}`);
                const title = document.getElementById(`title-${habitId}`);
                const habitItem = document.getElementById(`habit-item-${habitId}`);
                
                // Ambil timestamp sinkronisasi terakhir dari attribute HTML
                const lastSyncedAt = habitItem.getAttribute('data-updated-at');
                
                // Optimistic visual update
                const isCompleted = !checkbox.classList.contains('bg-emerald-500');
                if (isCompleted) {
                    checkbox.className = 'w-6 h-6 rounded-full flex items-center justify-center shadow-md transition-all duration-300 bg-emerald-500 text-white';
                    checkbox.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>`;
                    title.className = 'text-sm font-semibold transition-all duration-300 line-through text-gray-400 dark:text-gray-500';
                } else {
                    checkbox.className = 'w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-full hover:border-emerald-500 transition-all duration-300';
                    checkbox.innerHTML = '';
                    title.className = 'text-sm font-semibold transition-all duration-300 text-gray-800 dark:text-gray-200';
                }

                // Kirim request ke server beserta payload last_synced_at
                axios.post(`/boards/${boardId}/habits/${habitId}/toggle`, { last_synced_at: lastSyncedAt })
                    .then(response => {
                        // Update attribute data-updated-at lokal dengan waktu baru dari database
                        habitItem.setAttribute('data-updated-at', response.data.habit.updated_at);
                        
                        prependVersionLog({
                            id: Date.now(),
                            description: `${currentUserName} menandai tugas "${title.innerText.trim()}" sebagai ${isCompleted ? 'selesai' : 'belum selesai'}.`,
                            time: 'Baru saja'
                        });
                    })
                    .catch(err => {
                        // --- HANDLING CONFLICT 409 ---
                        if (err.response && err.response.status === 409) {
                            const current = err.response.data.current_habit;
                            
                            // Revert Tampilan (Kembali ke status di database)
                            if (current.is_completed) {
                                checkbox.className = 'w-6 h-6 rounded-full flex items-center justify-center shadow-md transition-all duration-300 bg-emerald-500 text-white';
                                checkbox.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>`;
                                title.className = 'text-sm font-semibold transition-all duration-300 line-through text-gray-400 dark:text-gray-500';
                            } else {
                                checkbox.className = 'w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-full hover:border-emerald-500 transition-all duration-300';
                                checkbox.innerHTML = '';
                                title.className = 'text-sm font-semibold transition-all duration-300 text-gray-800 dark:text-gray-200';
                            }

                            // Update data-updated-at agar sesuai dengan database
                            habitItem.setAttribute('data-updated-at', current.updated_at);

                            // Animasi getar (premium feel)
                            habitItem.classList.add('shake');
                            setTimeout(() => habitItem.classList.remove('shake'), 400);

                            showAlert('Konflik! Tugas sudah diubah terlebih dahulu oleh ' + current.updated_by_name + '.', 'error');
                        } else {
                            console.error(err);
                            showAlert('Gagal mengubah status habit.', 'error');
                        }
                    });
            };

            // --- AJAX: Delete Habit ---
            window.deleteHabit = function(habitId) {
                if (!confirm('Hapus habit ini?')) return;

                const habitElement = document.getElementById(`habit-item-${habitId}`);
                const habitTitle = document.getElementById(`title-${habitId}`).innerText.trim();
                habitElement.remove();

                axios.delete(`/boards/${boardId}/habits/${habitId}`)
                    .then(response => {
                        showAlert('Habit berhasil dihapus!');
                        prependVersionLog({
                            id: Date.now(),
                            description: `${currentUserName} menghapus tugas "${habitTitle}".`,
                            time: 'Baru saja'
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        showAlert('Gagal menghapus habit.', 'error');
                    });
            };

            // --- Live Activity: Focus / Hover Whispering ---
            document.addEventListener('mouseover', (e) => {
                const habitItem = e.target.closest('.habit-item');
                if (habitItem) {
                    const habitId = habitItem.dataset.id;
                    channel.whisper('active-interaction', {
                        userId: currentUserId,
                        userName: currentUserName,
                        habitId: habitId,
                        status: 'hovering'
                    });
                }
            });

            document.addEventListener('mouseout', (e) => {
                const habitItem = e.target.closest('.habit-item');
                if (habitItem) {
                    const habitId = habitItem.dataset.id;
                    channel.whisper('active-interaction', {
                        userId: currentUserId,
                        habitId: habitId,
                        status: 'idle'
                    });
                }
            });


            // --- Echo: PresenceChannel ---
            const channel = window.Echo.join(`board.${boardId}`)
                .here((users) => {
                    updateOnlineStatus(users);
                })
                .joining((user) => {
                    updateUserOnlineState(user.id, true, user.name);
                })
                .leaving((user) => {
                    updateUserOnlineState(user.id, false);
                })
                .listen('HabitEvent', (e) => {
                    // Update DOM jika perubahan dilakukan oleh user lain
                    if (e.userId !== currentUserId && e.habitData) {
                        handleRealtimeHabitUpdate(e);
                    }
                    // Append version history log
                    if (e.logData) {
                        prependVersionLog(e.logData);
                    }
                });

            // Listen to whispers (Live Activity)
            channel.listenForWhisper('active-interaction', (e) => {
                const activitySpan = document.getElementById(`activity-${e.habitId}`);
                if (activitySpan) {
                    if (e.status === 'hovering') {
                        activitySpan.innerText = `${e.userName} sedang aktif...`;
                        activitySpan.classList.remove('hidden');
                    } else {
                        activitySpan.classList.add('hidden');
                    }
                }
            });

            // --- Helper: Realtime DOM Updates ---
            function handleRealtimeHabitUpdate(e) {
                if (e.action === 'created') {
                    const placeholder = document.getElementById('no-habits-placeholder');
                    if (placeholder) placeholder.remove();

                    const div = document.createElement('div');
                    div.id = `habit-item-${e.habitData.id}`;
                    div.className = 'py-4 flex flex-col justify-center group habit-item';
                    div.dataset.id = e.habitData.id;
                    div.setAttribute('data-updated-at', e.habitData.updated_at);
                    div.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <button type="button" onclick="toggleHabit(${e.habitData.id})" class="focus:outline-none transition">
                                    <div id="checkbox-${e.habitData.id}" class="w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-full hover:border-emerald-500 transition-all duration-300"></div>
                                </button>
                                <span id="title-${e.habitData.id}" class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    ${e.habitData.title}
                                </span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span id="activity-${e.habitData.id}" class="hidden text-[10px] text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-full animate-pulse"></span>
                                <span id="updater-${e.habitData.id}" class="text-[10px] text-gray-400 italic bg-gray-50 px-2 py-1 rounded">
                                    Update: ${e.habitData.updated_by_name}
                                </span>
                                <button type="button" onclick="deleteHabit(${e.habitData.id})" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition duration-150">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    `;
                    habitList.appendChild(div);
                } else if (e.action === 'updated') {
                    const checkbox = document.getElementById(`checkbox-${e.habitData.id}`);
                    const title = document.getElementById(`title-${e.habitData.id}`);
                    const updater = document.getElementById(`updater-${e.habitData.id}`);
                    const habitItem = document.getElementById(`habit-item-${e.habitData.id}`);

                    if (checkbox && title) {
                        if (e.habitData.is_completed) {
                            checkbox.className = 'w-6 h-6 rounded-full flex items-center justify-center shadow-md transition-all duration-300 bg-emerald-500 text-white';
                            checkbox.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>`;
                            title.className = 'text-sm font-semibold transition-all duration-300 line-through text-gray-400 dark:text-gray-500';
                        } else {
                            checkbox.className = 'w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-full hover:border-emerald-500 transition-all duration-300';
                            checkbox.innerHTML = '';
                            title.className = 'text-sm font-semibold transition-all duration-300 text-gray-800 dark:text-gray-200';
                        }
                    }
                    if (updater) {
                        updater.innerText = `Update: ${e.habitData.updated_by_name}`;
                        updater.classList.remove('hidden');
                    }
                    if (habitItem) {
                        // Sangat penting: Update attribute updated-at lokal agar client ini tidak memicu konflik palsu ke depannya!
                        habitItem.setAttribute('data-updated-at', e.habitData.updated_at);
                    }
                } else if (e.action === 'deleted') {
                    const el = document.getElementById(`habit-item-${e.habitData.id}`);
                    if (el) el.remove();
                }
            }

            // --- Helper: Online UI status ---
            function updateOnlineStatus(users) {
                const dots = document.querySelectorAll('[id^="online-dot-"]');
                dots.forEach(dot => {
                    dot.className = 'w-2.5 h-2.5 bg-gray-300 dark:bg-gray-600 rounded-full';
                    dot.title = 'Offline';
                });
                
                userBadges.innerHTML = '';

                Object.values(users).forEach(user => {
                    const dot = document.getElementById(`online-dot-${user.id}`);
                    if (dot) {
                        dot.className = 'w-2.5 h-2.5 bg-emerald-500 rounded-full shadow';
                        dot.title = 'Online';
                    }

                    const badge = document.createElement('div');
                    badge.className = 'inline-flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-800 bg-indigo-600 text-white text-[10px] font-bold uppercase shadow';
                    badge.innerText = user.name.charAt(0);
                    badge.title = user.name + ' (Online)';
                    userBadges.appendChild(badge);
                });
            }

            function updateUserOnlineState(userId, isOnline, name = '') {
                const dot = document.getElementById(`online-dot-${userId}`);
                if (dot) {
                    if (isOnline) {
                        dot.className = 'w-2.5 h-2.5 bg-emerald-500 rounded-full shadow';
                        dot.title = 'Online';
                    } else {
                        dot.className = 'w-2.5 h-2.5 bg-gray-300 dark:bg-gray-600 rounded-full';
                        dot.title = 'Offline';
                    }
                }
                
                setTimeout(() => {
                    const activeUsers = window.Echo.join(`board.${boardId}`).users;
                    if(activeUsers) {
                        updateOnlineStatus(activeUsers);
                    }
                }, 200);
            }
        });
    </script>
    @endpush
</x-app-layout>
