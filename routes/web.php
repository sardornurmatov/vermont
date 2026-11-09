<?php

use App\Http\Controllers\Admin\OrderReviewController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\ProductTranslationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CourierController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShopController::class, 'index'])->name('shop.index');
Route::get('/ayollar', [ShopController::class, 'women'])->name('shop.women');
Route::get('/erkaklar', [ShopController::class, 'men'])->name('shop.men');
Route::get('/aksessuarlar', [ShopController::class, 'accessories'])->name('shop.accessories');
Route::get('/mahsulot/{slug}', [ShopController::class, 'show'])->name('shop.show');

Route::get('/til/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['uz', 'ru', 'en']), 404);
    session(['locale' => $locale]);
    app()->setLocale($locale);

    return back();
})->name('locale');

Route::get('/kirish', [LoginController::class, 'create'])->name('login');
Route::post('/kirish', [LoginController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('login.store');

Route::post('/chiqish', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/royxatdan-otish', [RegistrationController::class, 'create'])
    ->name('register');

Route::get('/royxatdan-otish/tekshiruv', [RegistrationController::class, 'availability'])
    ->name('register.availability');

Route::post('/royxatdan-otish', [RegistrationController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('register.store');

Route::get('/email-tasdiqlash', [RegistrationController::class, 'notice'])
    ->name('verification.notice');
Route::post('/email-tasdiqlash', [RegistrationController::class, 'verify'])
    ->middleware('throttle:20,1')
    ->name('verification.verify');
Route::post('/email-tasdiqlash/qayta-yuborish', [RegistrationController::class, 'resend'])
    ->middleware('throttle:2,1')
    ->name('verification.resend');

Route::get('/savat', [CartController::class, 'index'])->name('cart.index');
Route::post('/savat', [CartController::class, 'add'])->name('cart.add');
Route::patch('/savat', [CartController::class, 'update'])->name('cart.update');

Route::post('/mahsulot/{product:slug}/izohlar', [ReviewController::class, 'store'])
    ->middleware('auth')
    ->name('reviews.store');

Route::middleware('auth')->group(function () {
    Route::get('/akkaunt/buyurtmalar', [AccountController::class, 'orders'])
        ->name('account.orders');
    Route::get('/buyurtma', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/buyurtma', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/buyurtmalar/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/buyurtmalar/{order}/chek', [OrderController::class, 'receipt'])
        ->name('orders.receipt');
});

Route::prefix('kuryer')
    ->name('courier.')
    ->middleware(['auth', 'courier'])
    ->group(function () {
        Route::get('/buyurtmalar', [CourierController::class, 'index'])->name('orders.index');
        Route::patch('/buyurtmalar/{order}', [CourierController::class, 'update'])->name('orders.update');
    });

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // Hozircha boshqaruv paneli buyurtmalar sahifasiga yo‘naltiriladi.
        Route::get('/dashboard', function () {
            return redirect()->route('admin.orders.index');
        })->name('dashboard');

        Route::get('/buyurtmalar', [OrderReviewController::class, 'index'])
            ->name('orders.index');
        Route::get('/buyurtmalar/{order}/chek', [OrderReviewController::class, 'receipt'])
            ->name('orders.receipt');
        Route::patch('/buyurtmalar/{order}', [OrderReviewController::class, 'update'])
            ->name('orders.update');
        Route::post('/buyurtmalar/{order}/yetkazish', [OrderReviewController::class, 'assignDelivery'])
            ->name('orders.delivery');

        Route::get('/mahsulotlar', [ProductAdminController::class, 'index'])
            ->name('products.index');

        // Alohida edit-list sahifasi yo‘q bo‘lsa, mavjud mahsulotlar sahifasiga yo‘naltiradi.
        Route::get('/mahsulotlar/tahrirlash', function () {
            return redirect()->route('admin.products.index');
        })->name('products.edit-list');

        Route::post('/kategoriyalar', [ProductAdminController::class, 'storeCategory'])
            ->name('categories.store');
        Route::post('/mahsulot-tarjima', [ProductTranslationController::class, '__invoke'])
            ->name('products.translate');
        Route::post('/mahsulotlar', [ProductAdminController::class, 'store'])
            ->name('products.store');
        Route::patch('/mahsulotlar/{product}', [ProductAdminController::class, 'update'])
            ->name('products.update');
        Route::delete('/mahsulotlar/{product}', [ProductAdminController::class, 'destroy'])
            ->name('products.destroy');
        Route::put('/sozlamalar/karta', [ProductAdminController::class, 'settings'])
            ->name('settings.payment');
    });