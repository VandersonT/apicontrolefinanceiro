<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

#Controllers
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransactionController;

/*----------------------------------User--------------------------------------*/
Route::post('/auth', [UserController::class, 'userAuthenticate']);
Route::post('/newUser', [UserController::class, 'regiterNewUser']);
Route::get('/confirmAccount/{id}', [UserController::class, 'confirmAccount']);
Route::post('/login', [UserController::class, 'loginAction']);
Route::put('/editUser', [UserController::class, 'editUser']);
Route::post('/editUserAvatar/{id}', [UserController::class, 'editUserAvatar']);
/*----------------------------------------------------------------------------*/

/*------------------------------Transactions---------------------------------*/
Route::get('/userTransactions/{id}', [TransactionController::class, 'getUserTransactions']);
Route::get('/getUserFinancialInfo/{id}', [TransactionController::class, 'getUserFinancialInfo']);
Route::post('/newTransaction', [TransactionController::class, 'sendNewTransition']);
Route::delete('/deleteTransition/{id}', [TransactionController::class, 'deleteTransition']);
/*----------------------------------------------------------------------------*/