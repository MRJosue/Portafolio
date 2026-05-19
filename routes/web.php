<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FinancialEntryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('themes.editorial-black'))->name('home');
Route::get('/editorial-black', [HomeController::class, 'editorialBlack'])->name('themes.editorial-black');
Route::get('/neo-industrial', [HomeController::class, 'neoIndustrial'])->name('themes.neo-industrial');
Route::get('/editorial-black/journal', [HomeController::class, 'journal'])->name('journal.index');
Route::get('/editorial-black/proyectos', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/editorial-black/proyectos/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::post('/newsletter', NewsletterController::class)->name('newsletter.store');
Route::post('/chat/start', [ChatController::class, 'start'])->name('chat.start');
Route::get('/chat/{chatSession}/messages', [ChatController::class, 'messages'])->name('chat.messages');
Route::post('/chat/{chatSession}/contact', [ChatController::class, 'contact'])->name('chat.contact');
Route::post('/chat/{chatSession}/messages', [ChatController::class, 'visitorMessage'])->name('chat.visitor-message');

Route::get('/dashboard', function () {
    return redirect()->route('admin.index');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/finanzas', [FinancialEntryController::class, 'calendar'])->name('admin.finances.calendar');
    Route::get('/admin/finanzas/{date}', [FinancialEntryController::class, 'day'])->name('admin.finances.day');
    Route::post('/admin/finanzas/{date}/entradas', [FinancialEntryController::class, 'store'])->name('admin.finances.entries.store');
    Route::patch('/admin/finanzas/{date}/entradas/{entry}', [FinancialEntryController::class, 'update'])->name('admin.finances.entries.update');
    Route::delete('/admin/finanzas/{date}/entradas/{entry}', [FinancialEntryController::class, 'destroy'])->name('admin.finances.entries.destroy');
    Route::post('/admin/posts', [AdminController::class, 'storePost'])->name('admin.posts.store');
    Route::post('/admin/projects', [AdminController::class, 'storeProject'])->name('admin.projects.store');
    Route::match(['post', 'patch'], '/admin/projects/{project}/image', [AdminController::class, 'updateProjectImage'])->name('admin.projects.image.update');
    Route::post('/admin/profile-card', [AdminController::class, 'updateProfileCard'])->name('admin.profile-card.update');
    Route::delete('/admin/projects/{project}', [AdminController::class, 'destroyProject'])->name('admin.projects.destroy');
    Route::post('/admin/chat/{chatSession}/reply', [ChatController::class, 'adminReply'])->name('admin.chat.reply');
    Route::delete('/admin/chat/{chatSession}', [ChatController::class, 'destroy'])->name('admin.chat.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
