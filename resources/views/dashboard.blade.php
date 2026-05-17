<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('boards.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                + Buat Board Baru
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Notifications -->
            @if (session('success'))
                <div class="bg-emerald-500 text-white p-4 rounded-lg shadow-md mb-6 flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-white hover:text-emerald-200 font-bold">&times;</button>
                </div>
            @endif

            <!-- My Habit Boards -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Papan Habit Saya (Owned)
                </h3>

                @if($myBoards->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400 italic">Kamu belum memiliki papan tugas. Silakan buat baru!</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($myBoards as $board)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    <h4 class="text-base font-bold text-gray-900 dark:text-white truncate">{{ $board->title }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2 h-10">{{ $board->description ?? 'Tidak ada deskripsi.' }}</p>
                                </div>
                                <div class="mt-6 flex justify-between items-center border-t border-gray-100 dark:border-gray-700 pt-4">
                                    <span class="text-xs text-gray-400 dark:text-gray-500">Dibuat {{ $board->created_at->diffForHumans() }}</span>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('boards.show', $board) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg text-xs font-bold transition">
                                            Buka Board
                                        </a>
                                        <form action="{{ route('boards.destroy', $board) }}" method="POST" onsubmit="return confirm('Hapus board ini? Semua habit di dalamnya juga akan terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center p-1.5 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-950/50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Shared/Collaborative Boards -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Papan Kolaborasi (Shared)
                </h3>

                @if($sharedBoards->isEmpty())
                    <div class="text-center py-10">
                        <p class="text-gray-500 dark:text-gray-400 italic">Kamu belum diundang ke papan kolaborasi manapun.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($sharedBoards as $board)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-base font-bold text-gray-900 dark:text-white truncate">{{ $board->title }}</h4>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Kolaborator</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 line-clamp-2 h-10">{{ $board->description ?? 'Tidak ada deskripsi.' }}</p>
                                </div>
                                <div class="mt-6 flex justify-between items-center border-t border-gray-100 dark:border-gray-700 pt-4">
                                    <span class="text-xs text-gray-400 dark:text-gray-500">Pemilik: <strong>{{ $board->owner->name }}</strong></span>
                                    <a href="{{ route('boards.show', $board) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg text-xs font-bold transition">
                                        Buka Board
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
