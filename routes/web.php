<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\ChatController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [ViewController::class, 'registerView'])->name('register');
Route::get('/login', [ViewController::class, 'loginView'])->name('login');


Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::group(['middleware' => ['auth']], function() {    
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [ViewController::class, 'dashboardView'])->name('dashboard');
   });



// Route::view('/dashboard', 'dashboard')->name('dashboard');


Route::get('forget-password', [ViewController::class, 'ForgetPasswordForm'])->name('forget.password.get');
Route::post('forget-password', [AuthController::class, 'submitForgetPasswordForm'])->name('forget.password.post'); 
Route::get('reset-password/{token}', [ViewController::class, 'ResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [AuthController::class, 'submitResetPasswordForm'])->name('reset.password.post');

Route::get('/fetch-users', [ChatController::class, 'fetchUsers'])->name('fetch.users');
Route::post('/start-chat', [ChatController::class, 'startChat'])->name('start.chat');
Route::post('/get-chats', [ChatController::class, 'getChats'])->name('get.chats');

Route::post('/get-chat-messages', [ChatController::class, 'getChatMessages'])->name('get.chat.messages');

// Route::post('/get-chat-messages/{userId}/{recieverId}', [ChatController::class, 'getChatMessages'])->name('get.chat.messages');

Route::post('/send-message', [ChatController::class, 'sendMessage'])->name('send.message');
