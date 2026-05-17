<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        Habit::create([
            'board_id' => $board->id,
            'title' => $validated['title'],
            'is_completed' => false,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Habit berhasil ditambahkan!');
    }

    public function toggle(HabitBoard $board, Habit $habit)
    {
        // Check access
        if ($board->owner_id !== Auth::id() && !$board->collaborators->contains(Auth::id())) {
            abort(403);
        }

        $habit->update([
            'is_completed' => !$habit->is_completed,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Status habit berhasil diubah!');
    }

    public function destroy(HabitBoard $board, Habit $habit)
    {
        // Check access
        if ($board->owner_id !== Auth::id() && !$board->collaborators->contains(Auth::id())) {
            abort(403);
        }

        $habit->delete();

        return back()->with('success', 'Habit berhasil dihapus!');
    }
}
