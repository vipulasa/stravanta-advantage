<?php

use App\Http\Controllers\ContactSubmissionController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::inertia('/', 'welcome')->name('home');
Route::inertia('contact', 'contact')->name('contact');

Route::post('contact', ContactSubmissionController::class)
    ->middleware([ProtectAgainstSpam::class, 'throttle:5,1'])
    ->name('contact.store');
