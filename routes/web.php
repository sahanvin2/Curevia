<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EncyclopediaController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ChatbotController;

use App\Http\Controllers\PageController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Static Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/advertise', [PageController::class, 'advertise'])->name('advertise');
Route::match(['get','post'], '/contact', [PageController::class, 'contact'])->name('contact');

// Encyclopedia
Route::get('/encyclopedia', [EncyclopediaController::class, 'index'])->name('encyclopedia.index');
Route::get('/encyclopedia/{slug}', [EncyclopediaController::class, 'show'])->name('encyclopedia.show');

// Stories
Route::get('/stories', [StoryController::class, 'index'])->name('stories.index');
Route::get('/stories/{slug}', [StoryController::class, 'show'])->name('stories.show');

// Shop
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');

// Discover
Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');

// Search API
Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');

// Chatbot API
Route::post('/api/chatbot', [ChatbotController::class, 'send'])->name('api.chatbot');
Route::post('/api/chatbot/share', [ChatbotController::class, 'shareAsPost'])->middleware('auth')->name('api.chatbot.share');

// Full-page Chatbot
Route::get('/chat', [ChatbotController::class, 'index'])->name('chat');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Product management
    Route::get('/products', [AdminController::class, 'productsIndex'])->name('admin.products.index');
    Route::get('/products/create', [AdminController::class, 'productsCreate'])->name('admin.products.create');
    Route::post('/products', [AdminController::class, 'productsStore'])->name('admin.products.store');
    Route::get('/products/{product}/edit', [AdminController::class, 'productsEdit'])->name('admin.products.edit');
    Route::put('/products/{product}', [AdminController::class, 'productsUpdate'])->name('admin.products.update');
    Route::delete('/products/{product}', [AdminController::class, 'productsDestroy'])->name('admin.products.destroy');
});
