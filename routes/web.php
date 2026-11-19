<?php

use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LibraryController::class, 'index'])->name('dashboard');
Route::post('/books', [LibraryController::class, 'storeBook'])->name('books.store');
Route::post('/members', [LibraryController::class, 'storeMember'])->name('members.store');
Route::post('/loans', [LibraryController::class, 'storeLoan'])->name('loans.store');
Route::post('/loans/{loan}/return', [LibraryController::class, 'returnLoan'])
    ->whereNumber('loan')
    ->name('loans.return');
Route::post('/fines', [LibraryController::class, 'storeFine'])->name('fines.store');
Route::post('/fines/{fine}/pay', [LibraryController::class, 'payFine'])
    ->whereNumber('fine')
    ->name('fines.pay');
