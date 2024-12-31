<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/increase-quantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.qty.increase');
Route::put('/cart/decrease-quantity/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.qty.decrease');
Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
Route::delete('/cart/empty', [CartController::class, 'empty_cart'])->name('cart.empty');

Route::post('/cart/apply-coupon', [CartController::class, 'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon', [CartController::class, 'remove_coupon_code'])->name('cart.coupon.remove');

Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/place-order', [CartController::class, 'place_order'])->name('cart.place.order');
Route::get('/order-confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
// Route::put('/wishlist/increase-quantity/{rowId}', [WishlistController::class, 'increase_wishlist_quantity'])->name('wishlist.qty.increase');
// Route::put('/wishlist/decrease-quantity/{rowId}', [WishlistController::class, 'decrease_wishlist_quantity'])->name('wishlist.qty.decrease');
Route::delete('/wishlist/remove/{rowId}', [WishlistController::class, 'remove_item'])->name('wishlist.item.remove');
Route::delete('/wishlist/empty', [WishlistController::class, 'empty_wishlist'])->name('wishlist.empty');
Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'move_to_cart'])->name('wishlist.item.move');

Route::get('/contact-us', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact/store', [HomeController::class, 'store_contact'])->name('contact.store');

Route::get('/search', [HomeController::class, 'search'])->name('home.search');

Route::middleware('auth')->group(function ()
{
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-order-details/{id}', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/account-order/cancel-order', [UserController::class, 'cancel_order'])->name('user.order.cancel');
});

Route::middleware(['auth', AuthAdmin::class])->group(function ()
{
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('/admin/brand/store', [AdminController::class, 'store_brand'])->name('admin.brand.store');
    Route::get('/admin/brand/edit/{id}', [AdminController::class, 'edit_brand'])->name('admin.brand.edit');
    Route::put('/admin/brand/update', [AdminController::class, 'update_brand'])->name('admin.brand.update');
    Route::delete('admin/brand/delete/{id}', [AdminController::class, 'delete_brand'])->name('admin.brand.delete');

    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/category/add', [AdminController::class, 'add_category'])->name('admin.category.add');
    Route::post('admin/category/store', [AdminController::class, 'store_category'])->name('admin.category.store');
    Route::get('admin/category/edit/{id}', [AdminController::class, 'edit_category'])->name('admin.category.edit');
    Route::put('admin/category/update', [AdminController::class, 'update_category'])->name('admin.category.update');
    Route::delete('admin/category/delete/{id}', [AdminController::class, 'delete_category'])->name('admin.category.delete');

    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/product/add', [AdminController::class, 'add_product'])->name('admin.product.add');
    Route::post('/admin/product/store', [AdminController::class, 'store_product'])->name('admin.product.store');
    Route::get('/admin/product/edit/{id}', [AdminController::class, 'edit_product'])->name('admin.product.edit');
    Route::put('/admin/product/update', [AdminController::class, 'update_product'])->name('admin.product.update');
    Route::delete('/admin/product/delete/{id}', [AdminController::class, 'delete'])->name('admin.product.delete');

    Route::get('/admin/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
    Route::get('/admin/coupon/add', [AdminController::class, 'add_coupon'])->name('admin.coupon.add');
    Route::post('/admin/coupon/store', [AdminController::class, 'store_coupon'])->name('admin.coupon.store');
    Route::get('/admin/coupon/edit/{id}', [AdminController::class, 'edit_coupon'])->name('admin.coupon.edit');
    Route::put('/admin/coupon/update', [AdminController::class, 'update_coupon'])->name('admin.coupon.update');
    Route::delete('/admin/coupon/delete/{id}', [AdminController::class, 'delete_coupon'])->name('admin.coupon.delete');

    Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/admin/order-details/{id}', [AdminController::class, 'show_order'])->name('admin.order.details');
    Route::put('/admin/order-status/update', [AdminController::class, 'update_order_status'])->name('admin.order.status.update');

    Route::get('/admin/slides', [AdminController::class, 'slides'])->name('admin.slides');
    Route::get('/admin/slide/add', [AdminController::class, 'add_slide'])->name('admin.slide.add');
    Route::post('/admin/slide/store', [AdminController::class, 'store_slide'])->name('admin.slide.store');
    Route::get('admin/slide/edit/{id}', [AdminController::class, 'edit_slide'])->name('admin.slide.edit');
    Route::put('admin/slide/update', [AdminController::class, 'update_slide'])->name('admin.slide.update');
    Route::delete('admin/slide/delete/{id}', [AdminController::class, 'delete_slide'])->name('admin.slide.delete');

    Route::get('/admin/contact', [AdminController::class, 'contacts'])->name('admin.contacts');
    Route::delete('/admin/contact/delete/{id}', [AdminController::class, 'delete_contact'])->name('admin.contact.delete');

    Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');
});
