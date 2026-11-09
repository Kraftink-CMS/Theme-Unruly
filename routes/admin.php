<?php

use Illuminate\Support\Facades\Route;
use KraftInk\Themes\Unruly\Http\Controllers\Admin\AdminController;

Route::prefix('admin/themes/unruly')
    ->name('themes.unruly.admin.configuration.')
    ->group(function (): void {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/', [AdminController::class, 'save'])->name('update');
    });
