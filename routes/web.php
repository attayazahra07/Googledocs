<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HabitBoardController;
use App\Http\Controllers\HabitController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [HabitBoardController::class, 'index'])->name('dashboard');
    
    // Board CRUD & Invitations
    Route::resource('boards', HabitBoardController::class);
    Route::post('/boards/{board}/invite', [HabitBoardController::class, 'invite'])->name('boards.invite');
    
    // Habit CRUD (nested in boards)
    Route::post('/boards/{board}/habits', [HabitController::class, 'store'])->name('habits.store');
    Route::post('/boards/{board}/habits/{habit}/toggle', [HabitController::class, 'toggle'])->name('habits.toggle');
    Route::delete('/boards/{board}/habits/{habit}', [HabitController::class, 'destroy'])->name('habits.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
