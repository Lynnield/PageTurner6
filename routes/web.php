<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/dashboard/public', [\App\Http\Controllers\PublicDashboardController::class, 'index'])
    ->name('dashboard.public');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('customer.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'twofactor'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/security/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'settings'])->name('two-factor.settings');
    Route::post('/security/two-factor/enable-email', [\App\Http\Controllers\TwoFactorController::class, 'enableEmail'])->name('two-factor.enable-email');
    Route::post('/security/two-factor/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('two-factor.disable');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{book}', [CartController::class, 'remove'])->name('cart.remove');

    Route::middleware('verified')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

        Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/two-factor/challenge', [\App\Http\Controllers\TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('two-factor.verify');
});

// Admin Routes
Route::middleware(['auth', 'verified', EnsureUserIsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('books', BookController::class);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

    // Import / Export (enterprise)
    Route::get('/imports', [\App\Http\Controllers\Admin\ImportExportController::class, 'listImports'])->name('imports.index');
    Route::get('/imports/{importLog}', [\App\Http\Controllers\Admin\ImportExportController::class, 'showImport'])->name('imports.show');
    Route::post('/books/import', [\App\Http\Controllers\Admin\ImportExportController::class, 'importBooks'])->name('books.import');

    Route::get('/exports', [\App\Http\Controllers\Admin\ImportExportController::class, 'listExports'])->name('exports.index');
    Route::post('/books/export', [\App\Http\Controllers\Admin\ImportExportController::class, 'exportBooks'])->name('books.export');
    Route::get('/exports/{exportLog}/download', [\App\Http\Controllers\Admin\ImportExportController::class, 'downloadExport'])->name('exports.download');
});

Route::middleware(['auth', 'verified'])->get('/customer/dashboard', [\App\Http\Controllers\CustomerDashboardController::class, 'index'])->name('customer.dashboard');
require __DIR__.'/auth.php';
