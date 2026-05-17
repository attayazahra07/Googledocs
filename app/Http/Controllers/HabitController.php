<?php

namespace App\Http\Controllers;

use App\Events\HabitEvent;
use App\Models\Habit;
use App\Models\HabitBoard;
use App\Models\BoardVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HabitController extends Controller
{
    public function store(Request $request, HabitBoard $board)
    {
        // Check access
        if ($board->owner_id !== Auth::id() && !$board->collaborators->contains(Auth::id())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $habit = Habit::create([
            'board_id' => $board->id,
            'title' => $validated['title'],
            'is_completed' => false,
            'updated_by' => Auth::id(),
        ]);

        // Log to Version History
        $log = BoardVersion::create([
            'board_id' => $board->id,
            'user_id' => Auth::id(),
            'action' => 'added_habit',
            'description' => Auth::user()->name . ' menambahkan tugas baru: "' . $habit->title . '".',
        ]);

        // Broadcast to others (wrapped in try-catch to prevent app crash when Reverb server is offline)
        try {
            broadcast(new HabitEvent([
                'id' => $habit->id,
                'title' => $habit->title,
                'is_completed' => $habit->is_completed,
                'updated_at' => $habit->updated_at->toISOString(),
                'updated_by_name' => Auth::user()->name,
            ], 'created', $board->id, Auth::id(), [
                'id' => $log->id,
                'description' => $log->description,
                'time' => $log->created_at->diffForHumans(),
                'user_name' => Auth::user()->name,
            ]))->toOthers();
        } catch (\Exception $e) {
            // Ignore socket connection errors gracefully if Reverb server isn't started yet
        }

        return back()->with('success', 'Habit berhasil ditambahkan!');
    }

    public function toggle(Request $request, HabitBoard $board, Habit $habit)
    {
        // Check access
        if ($board->owner_id !== Auth::id() && !$board->collaborators->contains(Auth::id())) {
            abort(403);
        }

        // --- CONFLICT RESOLUTION LOGIC ---
        if ($request->has('last_synced_at')) {
            $clientSyncedAt = Carbon::parse($request->input('last_synced_at'));
            $dbUpdatedAt = $habit->updated_at;

            // Jika data di DB ternyata lebih baru daripada yang diketahui client (selisih > 1 detik untuk toleransi)
            if ($dbUpdatedAt->gt($clientSyncedAt->addSecond())) {
                // Catat Konflik di Riwayat Versi
                $log = BoardVersion::create([
                    'board_id' => $board->id,
                    'user_id' => Auth::id(),
                    'action' => 'conflict_resolved',
                    'description' => 'Konflik Terresolusi! Perubahan status tugas "' . $habit->title . '" oleh ' . Auth::user()->name . ' dibatalkan karena data sudah diperbarui terlebih dahulu oleh pengguna lain.',
                ]);

                // Siarkan event konflik (wrapped in try-catch)
                try {
                    broadcast(new HabitEvent(null, 'conflict', $board->id, Auth::id(), [
                        'id' => $log->id,
                        'description' => $log->description,
                        'time' => $log->created_at->diffForHumans(),
                        'user_name' => Auth::user()->name,
                    ]))->toOthers();
                } catch (\Exception $e) {
                    // Gracefully ignore offline socket
                }

                // Kembalikan error 409 Conflict
                return response()->json([
                    'message' => 'Konflik terdeteksi! Tugas telah diubah oleh orang lain.',
                    'current_habit' => [
                        'id' => $habit->id,
                        'is_completed' => $habit->is_completed,
                        'updated_at' => $habit->updated_at->toISOString(),
                        'updated_by_name' => $habit->updater->name ?? 'System',
                    ]
                ], 409);
            }
        }

        // Jika tidak ada konflik, proses update seperti biasa
        $habit->update([
            'is_completed' => !$habit->is_completed,
            'updated_by' => Auth::id(),
        ]);

        $statusText = $habit->is_completed ? 'selesai' : 'belum selesai';

        // Log to Version History
        $log = BoardVersion::create([
            'board_id' => $board->id,
            'user_id' => Auth::id(),
            'action' => 'toggled_habit',
            'description' => Auth::user()->name . ' menandai tugas "' . $habit->title . '" sebagai ' . $statusText . '.',
        ]);

        // Broadcast to others (wrapped in try-catch)
        try {
            broadcast(new HabitEvent([
                'id' => $habit->id,
                'title' => $habit->title,
                'is_completed' => $habit->is_completed,
                'updated_at' => $habit->updated_at->toISOString(),
                'updated_by_name' => Auth::user()->name,
            ], 'updated', $board->id, Auth::id(), [
                'id' => $log->id,
                'description' => $log->description,
                'time' => $log->created_at->diffForHumans(),
                'user_name' => Auth::user()->name,
            ]))->toOthers();
        } catch (\Exception $e) {
            // Gracefully ignore offline socket
        }

        return response()->json([
            'message' => 'Status habit berhasil diubah!',
            'habit' => [
                'id' => $habit->id,
                'is_completed' => $habit->is_completed,
                'updated_at' => $habit->updated_at->toISOString(),
                'updated_by_name' => Auth::user()->name,
            ]
        ]);
    }

    public function destroy(HabitBoard $board, Habit $habit)
    {
        // Check access
        if ($board->owner_id !== Auth::id() && !$board->collaborators->contains(Auth::id())) {
            abort(403);
        }

        $habitId = $habit->id;
        $habitTitle = $habit->title;
        $habit->delete();

        // Log to Version History
        $log = BoardVersion::create([
            'board_id' => $board->id,
            'user_id' => Auth::id(),
            'action' => 'deleted_habit',
            'description' => Auth::user()->name . ' menghapus tugas "' . $habitTitle . '".',
        ]);

        // Broadcast to others (wrapped in try-catch)
        try {
            broadcast(new HabitEvent([
                'id' => $habitId,
            ], 'deleted', $board->id, Auth::id(), [
                'id' => $log->id,
                'description' => $log->description,
                'time' => $log->created_at->diffForHumans(),
                'user_name' => Auth::user()->name,
            ]))->toOthers();
        } catch (\Exception $e) {
            // Gracefully ignore offline socket
        }

        return response()->json(['message' => 'Habit berhasil dihapus!']);
    }
}
