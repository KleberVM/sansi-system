<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BackupController\BackupController;

Route::get('/backup', [BackupController::class, 'index'])->name('backup');