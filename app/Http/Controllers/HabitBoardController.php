<?php

namespace App\Http\Controllers;

use App\Models\HabitBoard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HabitBoardController extends Controller
{
    public function index()
    {
        // Get boards owned by user
        $myBoards = HabitBoard::where('owner_id', Auth::id())->latest()->get();
        
        // Get boards where user is a collaborator
        $sharedBoards = Auth::user()->collaborations()->latest()->get();

        return view('dashboard', compact('myBoards', 'sharedBoards'));
    }

    public function create()
    {
        return view('boards.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $board = HabitBoard::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),
        ]);

        return redirect()->route('boards.show', $board)->with('success', 'Board berhasil dibuat!');
    }

    public function show(HabitBoard $board)
    {
        // Check access
        if ($board->owner_id !== Auth::id() && !$board->collaborators->contains(Auth::id())) {
            abort(403, 'Anda tidak memiliki akses ke board ini.');
        }

        $board->load(['habits', 'collaborators']);
        
        return view('boards.show', compact('board'));
    }

    public function destroy(HabitBoard $board)
    {
        if ($board->owner_id !== Auth::id()) {
            abort(403, 'Hanya pemilik yang bisa menghapus board.');
        }

        $board->delete();
        return redirect()->route('dashboard')->with('success', 'Board berhasil dihapus!');
    }

    public function invite(Request $request, HabitBoard $board)
    {
        if ($board->owner_id !== Auth::id()) {
            abort(403, 'Hanya pemilik yang bisa mengundang orang lain.');
        }

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak bisa mengundang diri sendiri.');
        }

        if ($board->collaborators->contains($user->id)) {
            return back()->with('error', 'User tersebut sudah ada di dalam board.');
        }

        $board->collaborators()->attach($user->id);

        return back()->with('success', $user->name . ' berhasil ditambahkan sebagai kolaborator!');
    }
}
