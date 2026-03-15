<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\MessageController;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [MessageController::class, 'index'])->name('chat.index');
    Route::post('/chat', [MessageController::class, 'store'])->name('chat.store');
    Route::post('/chat-connect', [MessageController::class, 'connectChat'])->name('chat.connect');
    Route::post('/chat-disconnect', [MessageController::class, 'disconnectChat'])->name('chat.disconnect');
    Route::post('/messages-status-update', [MessageController::class, 'messageStatusUpdate'])->name('message.status.update');
});

require __DIR__.'/settings.php';
