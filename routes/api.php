<?php
use App\Http\Controllers\{
    AdminController,
    UserController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
});
Route::post('/message', [UserController::class, 'message']);
Route::post('/code', [UserController::class, 'code']);
Route::post('/create', [UserController::class, 'create']);

// Route::group([
//     'middleware' => 'admin',
//     'prefix' => 'auth'
// ], function () {
//     Route::post('/register', [AdminController::class, 'register']);
//     Route::post('/blockUser', [AdminController::class, 'blockUser']);
//     Route::post('/deleteUser', [AdminController::class, 'deleteUser']);
//     Route::post('/activeUser', [AdminController::class, 'activeUser']);
// });

