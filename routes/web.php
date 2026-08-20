<?php

use App\Http\Controllers\BlogIndexController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\ContactSubmissionController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::inertia('/', 'welcome')->name('home');
Route::inertia('contact', 'contact')->name('contact');

Route::get('blog', BlogIndexController::class)->name('blog.index');
Route::get('blog/{post}', BlogPostController::class)->name('blog.show');

Route::post('contact', ContactSubmissionController::class)
    ->middleware([ProtectAgainstSpam::class, 'throttle:5,1'])
    ->name('contact.store');
