<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->middleware('public.cache')->name('home');
Route::get('/posts', [PublicController::class, 'posts'])->middleware('public.cache')->name('posts.index');
Route::get('/posts/{post:slug}', [PublicController::class, 'show'])->middleware(['post.views', 'public.cache'])->name('posts.show');
Route::get('/category/{category:slug}', [PublicController::class, 'category'])->middleware('public.cache')->name('categories.show');
Route::get('/tag/{tag:slug}', [PublicController::class, 'tag'])->middleware('public.cache')->name('tags.show');
Route::get('/about', [PublicController::class, 'page'])->middleware('public.cache')->defaults('page', 'about')->name('about');
Route::get('/author/youssef-youyou', [PublicController::class, 'author'])->middleware('public.cache')->name('author.youssef');
Route::get('/contact', [PublicController::class, 'page'])->middleware('public.cache')->defaults('page', 'contact')->name('contact');
Route::post('/contact', [PublicController::class, 'contact'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/privacy-policy', [PublicController::class, 'page'])->middleware('public.cache')->defaults('page', 'privacy-policy')->name('privacy');
Route::get('/terms', [PublicController::class, 'page'])->middleware('public.cache')->defaults('page', 'terms')->name('terms');
Route::get('/editorial-policy', [PublicController::class, 'page'])->middleware('public.cache')->defaults('page', 'editorial-policy')->name('editorial-policy');
Route::get('/affiliate-disclosure', [PublicController::class, 'page'])->middleware('public.cache')->defaults('page', 'affiliate-disclosure')->name('affiliate-disclosure');
Route::get('/tools', [PublicController::class, 'tools'])->middleware('public.cache')->name('tools.index');
Route::get('/best', [PublicController::class, 'moneyIndex'])->middleware('public.cache')->name('money.index');
Route::get('/best/{slug}', [PublicController::class, 'moneyShow'])->middleware('public.cache')->name('money.show');
Route::get('/work-with-me', [PublicController::class, 'services'])->middleware('public.cache')->name('services');
Route::get('/services', [PublicController::class, 'services'])->middleware('public.cache')->name('services.alias');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicController::class, 'robots'])->name('robots');
Route::get('/ads.txt', [PublicController::class, 'ads'])->name('ads');
Route::get('/feed.xml', [PublicController::class, 'feed'])->name('feed');
Route::get('/rss.xml', [PublicController::class, 'feed'])->name('rss');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('posts/bulk', [PostController::class, 'bulk'])->name('posts.bulk');
    Route::resource('posts', PostController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('create', 'show');
    Route::resource('tags', TagController::class)->except('create', 'show');
});
