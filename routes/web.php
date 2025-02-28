<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\ChatController;
use App\Http\Middleware\TrackUserStatus;


Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [ViewController::class, 'loginView'])->name('login');


Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [ViewController::class, 'registerView'])->name('register');

Route::group(['middleware' => ['auth']], function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [ViewController::class, 'dashboardView'])->name('dashboard')->middleware(TrackUserStatus::class);
    Route::get('/fetch-users', [ChatController::class, 'fetchUsers'])->name('fetch.users');
    Route::post('/start-chat', [ChatController::class, 'startChat'])->name('start.chat');
    Route::post('/get-chats', [ChatController::class, 'getChats'])->name('get.chats');

    Route::post('/get-chat-messages', [ChatController::class, 'getChatMessages'])->name('get.chat.messages');


    Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('send.message');
    Route::post('/user-details', [AuthController::class, 'getUserDetails'])->name('user.details');
    Route::post('/check-user-status', [AuthController::class, 'checkUserStatus'])->name('check.user.status');
});



// Route::view('/dashboard', 'dashboard')->name('dashboard');


Route::get('forget-password', [ViewController::class, 'ForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [AuthController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}', [ViewController::class, 'ResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [AuthController::class, 'submitResetPasswordForm'])->name('reset.password.post');
