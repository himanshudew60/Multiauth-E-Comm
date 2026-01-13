<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');





require __DIR__ . '/auth.php';

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Full CRUD routes for customers
    Route::post('/customers', [CustomerController::class, 'index'])->name('customers.search');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers/store', [CustomerController::class, 'store'])->name('customers.store');
    Route::match(['get', 'post'], '/customers/export/csv', [CustomerController::class, 'exportCSV'])->name('customers.export.csv');
    Route::match(['get', 'post'], '/customers/pdf', [CustomerController::class, 'exportPdfView'])->name('customers.pdf');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');





    Route::post('/categories', [CategoriesController::class, 'index'])->name('categories.search');
    Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
    Route::put('/categories/{categories}', [CategoriesController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{categories}', [CategoriesController::class, 'destroy'])->name('categories.destroy');
    Route::get('/categories/create', [CategoriesController::class, 'create'])->name('categories.create');
    Route::post('/categories/store', [CategoriesController::class, 'store'])->name('categories.store');
    Route::match(['get', 'post'], '/categories/export/csv', [CategoriesController::class, 'exportCSV'])->name('categories.export.csv');
    Route::match(['get', 'post'], '/categories/pdf', [CategoriesController::class, 'exportPdfView'])->name('categories.pdf');
    Route::get('/categories/{categories}/edit', [CategoriesController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/import', [CategoriesController::class, 'import'])->name('categories.import');





    Route::post('/tags', [TagsController::class, 'index'])->name('tags.search');
    Route::get('/tags', [TagsController::class, 'index'])->name('tags.index');
    Route::put('/tags/{tags}', [TagsController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tags}', [TagsController::class, 'destroy'])->name('tags.destroy');
    Route::get('/tags/create', [TagsController::class, 'create'])->name('tags.create');
    Route::post('/tags/store', [TagsController::class, 'store'])->name('tags.store');
    Route::match(['get', 'post'], '/tags/pdf', [TagsController::class, 'exportPdfView'])->name('tags.pdf');
    Route::match(['get', 'post'], '/tags/export/csv', action: [TagsController::class, 'exportCSV'])->name('tags.export.csv');
    Route::get('/tags/{tags}/edit', [TagsController::class, 'edit'])->name('tags.edit');
    Route::post('/tags/import', [TagsController::class, 'import'])->name('tags.import');




    Route::post('/products', [ProductController::class, 'index'])->name('products.search');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::put('/products/{products}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{products}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
    Route::match(['get', 'post'], '/products/export/csv', [ProductController::class, 'exportCSV'])->name('products.export.csv');
    Route::match(['get', 'post'], '/products/pdf', [ProductController::class, 'exportPdfView'])->name('products.pdf');
    Route::get('/products/{products}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{products}/edit', [ProductController::class, 'edit'])->name(name: 'products.edit');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');






    Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::put('/coupons/{coupons}', [CouponController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupons}', [CouponController::class, 'destroy'])->name('coupons.destroy');
    Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
    Route::post('/coupons/store', [CouponController::class, 'store'])->name('coupons.store');

    Route::get('/coupons/{coupons}/edit', [CouponController::class, 'edit'])->name(name: 'coupons.edit');

});


Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserController::class, 'index'])->name('dashboard');
    Route::match(['get','post'],'/info/{id}', [UserController::class, 'info'])->name('info');
    Route::get('/cart', [UserController::class, 'showCart'])->name('cart');
    Route::get('/add-to-cart/{id}', [UserController::class, 'addToCart'])->name('addToCart');
    Route::post('/cart/update/{id}', [UserController::class, 'updateCartQuantity'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [UserController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/user-info', [UserController::class, 'store'])->name('store');
    Route::get('/user-edit', [UserController::class, 'edit'])->name('edit');
    Route::get('/getuser-info', [UserController::class, 'info'])->name('info');
    Route::post('/buy-all', [UserController::class, 'buyAllCartItems'])->name('buyAll');
    Route::get('/my-orders', action: [UserController::class, 'myOrders'])->name('orders');  
    Route::get('/user/generate-bill/date/{date}', [UserController::class, 'generateBillByDate'])->name('generateBillByDate');
    
    Route::post('/cart/apply-coupon', [UserController::class, 'applyCoupon'])->name('cart.applyCoupon');

    Route::post('/user/validate-profile', [UserController::class, 'validateProfile'])->name('validate');


});
