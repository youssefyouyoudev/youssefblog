<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/posts', [PublicController::class, 'posts'])->name('posts.index');
Route::get('/posts/{post:slug}', [PublicController::class, 'show'])->name('posts.show');
Route::get('/category/{category:slug}', [PublicController::class, 'category'])->name('categories.show');
Route::get('/tag/{tag:slug}', [PublicController::class, 'tag'])->name('tags.show');
Route::get('/about', [PublicController::class, 'page'])->defaults('page', 'about')->name('about');
Route::get('/contact', [PublicController::class, 'page'])->defaults('page', 'contact')->name('contact');
Route::post('/contact', [PublicController::class, 'contact'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/privacy-policy', [PublicController::class, 'page'])->defaults('page', 'privacy-policy')->name('privacy');
Route::get('/terms', [PublicController::class, 'page'])->defaults('page', 'terms')->name('terms');
Route::get('/editorial-policy', [PublicController::class, 'page'])->defaults('page', 'editorial-policy')->name('editorial-policy');
Route::get('/affiliate-disclosure', [PublicController::class, 'page'])->defaults('page', 'affiliate-disclosure')->name('affiliate-disclosure');
Route::get('/tools', [PublicController::class, 'tools'])->name('tools.index');
Route::get('/work-with-me', [PublicController::class, 'services'])->name('services');
Route::get('/services', [PublicController::class, 'services'])->name('services.alias');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicController::class, 'robots'])->name('robots');
Route::get('/feed.xml', [PublicController::class, 'feed'])->name('feed');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('create', 'show');
    Route::resource('tags', TagController::class)->except('create', 'show');
});
