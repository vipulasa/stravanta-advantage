<?php

use App\Http\Controllers\BlogIndexController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\ContactPageController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::get('/', HomeController::class)->name('home');
Route::get('contact', ContactPageController::class)->name('contact');

Route::get('blog', BlogIndexController::class)->name('blog.index');
Route::get('blog/{post}', BlogPostController::class)->name('blog.show');

Route::post('contact', ContactSubmissionController::class)
    ->middleware([ProtectAgainstSpam::class, 'throttle:5,1'])
    ->name('contact.store');

// Served by the application rather than from `public/` so both can name the
// real domain — a static file cannot, and a file in `public/` would shadow
// these routes entirely.
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', RobotsController::class)->name('robots');
