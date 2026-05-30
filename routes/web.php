<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ChapterLikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ManhwaLikeController;
use App\Http\Controllers\ManhwaRatingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManhwaController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'sendContact'])->name('contact.send');
Route::get('/manhwa/{manhwa:slug}', [ManhwaController::class, 'show'])->name('manhwa.show');
Route::get('/manhwa/{manhwa:slug}/chapter/{chapterNumber}', [ChapterController::class, 'show'])
    ->where('chapterNumber', '[0-9]+')
    ->name('chapter.show');

Route::middleware('guest')->group(function () {
    Route::view('login', 'auth.login')->name('login');
    Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('logout', [GoogleAuthController::class, 'logout'])->name('logout');
    Route::post('/manhwa/{manhwa:slug}/bookmark', [BookmarkController::class, 'toggle'])
        ->name('manhwa.bookmark.toggle');
    Route::post('/manhwa/{manhwa:slug}/like', [ManhwaLikeController::class, 'toggle'])
        ->name('manhwa.like.toggle');
    Route::post('/manhwa/{manhwa:slug}/rate', [ManhwaRatingController::class, 'store'])
        ->name('manhwa.rate.store');
    Route::post('/manhwa/{manhwa:slug}/chapter/{chapterNumber}/like', [ChapterLikeController::class, 'toggle'])
        ->where('chapterNumber', '[0-9]+')
        ->name('chapter.like.toggle');
    Route::post('/manhwa/{manhwa:slug}/comments', [CommentController::class, 'storeManhwa'])
        ->name('manhwa.comments.store');
    Route::post('/manhwa/{manhwa:slug}/chapter/{chapterNumber}/comments', [CommentController::class, 'storeChapter'])
        ->where('chapterNumber', '[0-9]+')
        ->name('chapter.comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/react', [CommentController::class, 'react'])->name('comments.react');
});
