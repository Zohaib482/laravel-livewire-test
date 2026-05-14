<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/purchases', fn () => view('purchases.index'))
        ->middleware('can:view-purchases')
        ->name('purchases.index');

    Route::get('/purchases/create', fn () => view('purchases.form'))
        ->middleware('can:manage-purchases')
        ->name('purchases.create');

    Route::get('/purchases/{purchase}/edit', fn (App\Models\Purchase $purchase) => view('purchases.form', ['purchaseId' => $purchase->id]))
        ->middleware('can:manage-purchases')
        ->name('purchases.edit');

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])
        ->middleware('can:view-purchases')
        ->name('purchases.show');
});
