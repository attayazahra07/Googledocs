<?php

namespace App\Http\Controllers;

use App\Events\HabitEvent;
use App\Models\HabitBoard;
use App\Models\BoardVersion;
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

        // Initial creation log
        BoardVersion::create([
            'board_id' => $board->id,
            'user_id' => Auth::id(),
            'action' => 'created_board',
            'description' => Auth::user()->name . ' membuat papan tugas baru: "' . $board->title . '".',
        ]);

        return redirect()->route('boards.show', $board)->with('success', 'Board berhasil dibuat!');
    }

    public function show(HabitBoard $board)
    {
        // Check access
        if ($board->owner_id !== Auth::id() && !$board->collaborators->contains(Auth::id())) {
            abort(403, 'Anda tidak memiliki akses ke board ini.');
        }

        // Eager load habits, collaborators, and the last 15 history logs
        $board->load([
            'habits', 
            'collaborators', 
            'versions' => function ($query) {
                $query->with('user')->latest()->take(15);
            }
        ]);
        
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
        ], [
            'email.exists' => 'Email tersebut belum terdaftar di aplikasi. Minta temanmu untuk mendaftar (Register) akun terlebih dahulu!',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak bisa mengundang diri sendiri.');
        }

        if ($board->collaborators->contains($user->id)) {
            return back()->with('error', 'User tersebut sudah ada di dalam board.');
        }

        $board->collaborators()->attach($user->id);

        // Log to Version History
        $log = BoardVersion::create([
            'board_id' => $board->id,
            'user_id' => Auth::id(),
            'action' => 'invited_collaborator',
            'description' => Auth::user()->name . ' mengundang kolaborator baru: ' . $user->name . '.',
        ]);

        // Broadcast the invitation log (wrapped in try-catch to prevent app crash when Reverb is offline)
        try {
            broadcast(new HabitEvent(null, 'invited', $board->id, Auth::id(), [
                'id' => $log->id,
                'description' => $log->description,
                'time' => $log->created_at->diffForHumans(),
                'user_name' => Auth::user()->name,
            ]))->toOthers();

            // Broadcast real-time invitation card directly to collaborator's dashboard private channel
            broadcast(new \App\Events\BoardInvitationEvent($board, $user->id));
        } catch (\Exception $e) {
            // Gracefully ignore offline socket
        }

        return back()->with('success', $user->name . ' berhasil ditambahkan sebagai kolaborator!');
    }
}
