<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', fn () => redirect()->route('home', ['auth' => 'login']))->name('login');
Route::get('/register', fn () => redirect()->route('home', ['auth' => 'signup']))->name('register');
Route::get('/how-it-works', [HomeController::class, 'how'])->name('how');
Route::get('/skills', [HomeController::class, 'skills'])->name('skills');
Route::get('/explore', [HomeController::class, 'explore'])->name('explore');
Route::get('/offers/{serviceOffer}', [HomeController::class, 'offer'])->name('offers.show');
Route::middleware('auth')->group(function () {
    Route::get('/app', [HomeController::class, 'dashboard'])->name('app.dashboard');
    Route::get('/app/offers/create', [HomeController::class, 'createOffer'])->name('offers.create');
    Route::post('/app/offers', [HomeController::class, 'storeOffer'])->name('offers.store');
    Route::post('/offers/{serviceOffer}/book', [HomeController::class, 'book'])->name('offers.book');
    Route::post('/app/bookings/{booking}/confirm', [HomeController::class, 'confirmBooking'])->name('bookings.confirm');
    Route::post('/app/bookings/{booking}/settle', [HomeController::class, 'settleBooking'])->name('bookings.settle');
    Route::get('/operations', [HomeController::class, 'operations'])->name('operations.dashboard');
    Route::get('/admin', [HomeController::class, 'admin'])->name('admin.dashboard');
});
Route::get('/api/data', [HomeController::class, 'data']);
Route::post('/api/signup', [HomeController::class, 'signup']);
Route::post('/api/login', [HomeController::class, 'login']);
Route::post('/api/logout', [HomeController::class, 'logout'])->name('api.logout');
Route::post('/api/donate', [HomeController::class, 'donate']);
Route::post('/api/requests', [HomeController::class, 'requestHelp']);
Route::post('/api/requests/{helpRequest}/offer', [HomeController::class, 'offerHelp']);
