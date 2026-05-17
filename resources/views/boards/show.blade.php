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
            <div>
                <span class="text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400 px-3 py-1 rounded-full font-semibold">
                    Pemilik: {{ $board->owner->name }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Alert Notifications -->
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
                        <form action="{{ route('habits.store', $board) }}" method="POST" class="flex space-x-3">
                            @csrf
                            <input type="text" name="title" required placeholder="Tambah kebiasaan baru..." class="flex-grow rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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

                        @if($board->habits->isEmpty())
                            <div class="text-center py-10">
                                <p class="text-gray-500 dark:text-gray-400 italic">Belum ada tugas atau habit di papan ini.</p>
                            </div>
                        @else
                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($board->habits as $habit)
                                    <div class="py-4 flex items-center justify-between group">
                                        <div class="flex items-center space-x-3">
                                            <!-- Checkbox Form -->
                                            <form action="{{ route('habits.toggle', [$board, $habit]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="focus:outline-none transition">
                                                    @if($habit->is_completed)
                                                        <div class="w-6 h-6 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-md">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                        </div>
                                                    @else
                                                        <div class="w-6 h-6 border-2 border-gray-300 dark:border-gray-600 rounded-full hover:border-emerald-500 transition"></div>
                                                    @endif
                                                </button>
                                            </form>
                                            
                                            <!-- Title -->
                                            <span class="text-sm font-semibold transition-all duration-300 {{ $habit->is_completed ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-800 dark:text-gray-200' }}">
                                                {{ $habit->title }}
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <!-- Updater Status -->
                                            @if($habit->updater)
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 italic bg-gray-50 dark:bg-gray-700/30 px-2 py-1 rounded">
                                                    Update: {{ $habit->updater->name }}
                                                </span>
                                            @endif

                                            <!-- Delete Form -->
                                            <form action="{{ route('habits.destroy', [$board, $habit]) }}" method="POST" onsubmit="return confirm('Hapus habit ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition duration-150">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar Area (Collaborators) -->
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
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-widest rounded-lg shadow transition">
                                    Kirim Undangan
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Collaborator List -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6">
                        <h3 class="text-md font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Anggota Board
                        </h3>
                        <div class="space-y-3">
                            <!-- Owner -->
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-xs uppercase shadow">
                                    {{ substr($board->owner->name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $board->owner->name }}</span>
                                    <span class="text-[9px] text-indigo-500 uppercase tracking-widest font-bold">Pemilik</span>
                                </div>
                            </div>

                            <!-- Collaborators -->
                            @foreach($board->collaborators as $collab)
                                <div class="flex items-center space-x-3 border-t border-gray-100 dark:border-gray-700 pt-2">
                                    <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs uppercase shadow">
                                        {{ substr($collab->name, 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $collab->name }}</span>
                                        <span class="text-[9px] text-emerald-500 uppercase tracking-widest font-bold">Kolaborator</span>
                                    </div>
                                </div>
                            @endforeach

                            @if($board->collaborators->isEmpty())
                                <p class="text-xs text-gray-400 italic pt-2 border-t border-gray-100 dark:border-gray-700">Belum ada kolaborator.</p>
                            @endif
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
