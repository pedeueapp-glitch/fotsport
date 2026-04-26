<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\Photographer\SupportController as PhotographerSupport;
use App\Http\Controllers\Admin\SupportController as AdminSupport;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\HeroController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Customer store routes - Agora abertos ao público na página inicial
Route::get('/', [\App\Http\Controllers\StoreController::class, 'index'])->name('store.index');
Route::get('/eventos/busca', [\App\Http\Controllers\StoreController::class, 'searchEvents'])->name('store.search.events');
Route::get('/event/{event}', [\App\Http\Controllers\StoreController::class, 'showEvent'])->name('store.event');
Route::get('/search', fn() => redirect()->route('store.index'));
Route::post('/search', [\App\Http\Controllers\StoreController::class, 'search'])->name('store.search');
Route::get('/search-results', [\App\Http\Controllers\StoreController::class, 'showSearchResults'])->name('store.search.results');
Route::get('/checkout', fn() => redirect()->route('store.index'));
Route::post('/checkout', [\App\Http\Controllers\StoreController::class, 'checkout'])->name('store.checkout');
Route::post('/checkout/check-status', [\App\Http\Controllers\StoreController::class, 'checkPaymentStatus'])->name('store.checkout.check');
Route::get('/success', [\App\Http\Controllers\StoreController::class, 'success'])->name('store.success');
Route::get('/fotografos', [\App\Http\Controllers\StoreController::class, 'photographers'])->name('store.photographers');
Route::get('/fotografo/{slug}', [\App\Http\Controllers\StoreController::class, 'photographerPortfolio'])->name('store.photographer');
Route::post('/customer/login', [\App\Http\Controllers\CustomerAuthController::class, 'login'])->name('store.customer.login');
Route::post('/customer/logout', function () {
    auth()->guard('customer')->logout();
    return redirect()->route('store.index');
})->name('store.customer.logout');

Route::middleware('auth:customer')->group(function () {
    Route::get('/minhas-fotos', [\App\Http\Controllers\CustomerDashboardController::class, 'index'])->name('customer.dashboard');
    Route::get('/minhas-fotos/download/{purchase}', [\App\Http\Controllers\CustomerDashboardController::class, 'download'])->name('customer.download');
    Route::delete('/minhas-fotos/cancel/{purchase}', [\App\Http\Controllers\CustomerDashboardController::class, 'cancel'])->name('customer.cancel');
    Route::post('/minhas-fotos/repay/{purchase}', [\App\Http\Controllers\CustomerDashboardController::class, 'repay'])->name('customer.repay');
    Route::get('/minhas-fotos/checkout/{purchase}', [\App\Http\Controllers\CustomerDashboardController::class, 'showRepayCheckout'])->name('customer.repay.checkout');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        // Todos os fotógrafos veem todos os eventos para poder enviar fotos
        'events' => \App\Models\Event::with('user')
            ->withCount('photos')
            ->with(['photos' => function ($query) {
                $query->oldest()->limit(1);
            }])
            ->latest()
            ->get()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Photographer Financial
    Route::get('/financial', [\App\Http\Controllers\Photographer\FinancialController::class, 'index'])->name('financial.index');
    Route::post('/financial/pix', [\App\Http\Controllers\Photographer\FinancialController::class, 'updatePix'])->name('financial.updatePix');
    Route::post('/financial/withdraw', [\App\Http\Controllers\Photographer\FinancialController::class, 'withdraw'])->name('financial.withdraw');
    
    // SuperAdmin
    Route::prefix('admin')->group(function () {
        Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('admin.withdrawals.index');
        Route::post('/withdrawals/{withdrawal}/authorize', [\App\Http\Controllers\Admin\WithdrawalController::class, 'authorizeTransfer'])->name('admin.withdrawals.authorize');
        
        // Gestão de Fotógrafos
        Route::get('/photographers', [\App\Http\Controllers\Admin\AdminController::class, 'photographers'])->name('admin.photographers.index');
        Route::get('/photographers/{user}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editPhotographer'])->name('admin.photographers.edit');
        Route::patch('/photographers/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'updatePhotographer'])->name('admin.photographers.update');
        Route::patch('/photographers/{user}/verify', [\App\Http\Controllers\Admin\AdminController::class, 'toggleVerified'])->name('admin.photographers.verify');
        
        // Gestão de Eventos
        Route::get('/events', [\App\Http\Controllers\Admin\AdminController::class, 'events'])->name('admin.events.index');
        Route::get('/events/{event}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editEvent'])->name('admin.events.edit');
        Route::patch('/events/{event}', [\App\Http\Controllers\Admin\AdminController::class, 'updateEvent'])->name('admin.events.update');
        Route::delete('/events/{event}', [\App\Http\Controllers\Admin\AdminController::class, 'deleteEvent'])->name('admin.events.destroy');
        
        // Faturamento
        Route::get('/billing', [\App\Http\Controllers\Admin\AdminController::class, 'billing'])->name('admin.billing.index');
        
        // Gestão de Clientes
        Route::get('/customers', [\App\Http\Controllers\Admin\AdminController::class, 'customers'])->name('admin.customers.index');
        Route::get('/customers/{customer}/edit', [\App\Http\Controllers\Admin\AdminController::class, 'editCustomer'])->name('admin.customers.edit');
        Route::patch('/customers/{customer}', [\App\Http\Controllers\Admin\AdminController::class, 'updateCustomer'])->name('admin.customers.update');
        Route::get('/customers/{customer}/purchases', [\App\Http\Controllers\Admin\AdminController::class, 'customerPurchases'])->name('admin.customers.purchases');

        // Configurações
        Route::get('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('admin.settings.index');
        Route::patch('/settings', [\App\Http\Controllers\Admin\AdminController::class, 'updateSettings'])->name('admin.settings.update');

        // Marcas
        Route::get('/brands', [BrandController::class, 'index'])->name('admin.brands.index');
        Route::post('/brands', [BrandController::class, 'store'])->name('admin.brands.store');
        Route::post('/brands/{brand}', [BrandController::class, 'update'])->name('admin.brands.update');
        Route::patch('/brands/{brand}/toggle', [BrandController::class, 'toggle'])->name('admin.brands.toggle');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('admin.brands.destroy');

        // Carrossel Hero
        Route::get('/hero', [HeroController::class, 'index'])->name('admin.hero.index');
        Route::post('/hero', [HeroController::class, 'store'])->name('admin.hero.store');
        Route::post('/hero/{heroItem}', [HeroController::class, 'update'])->name('admin.hero.update');
        Route::delete('/hero/{heroItem}', [HeroController::class, 'destroy'])->name('admin.hero.destroy');
        Route::post('/hero/reorder', [HeroController::class, 'reorder'])->name('admin.hero.reorder');

        // Fotos de eventos (admin)
        Route::get('/events/{event}/photos', [\App\Http\Controllers\Admin\AdminController::class, 'eventPhotos'])->name('admin.events.photos');
        Route::delete('/events/{event}/photos/bulk', [\App\Http\Controllers\Admin\AdminController::class, 'bulkDestroyPhotos'])->name('admin.events.photos.bulk-destroy');
        Route::delete('/events/{event}/photos/{photo}', [\App\Http\Controllers\Admin\AdminController::class, 'destroyPhoto'])->name('admin.events.photos.destroy');
    });
    
    Route::resource('events', EventController::class);
    Route::post('/events/{event}/photos', [PhotoController::class, 'store'])->name('photos.store');
    Route::patch('/photos/{photo}/price', [PhotoController::class, 'updatePrice'])->name('photos.update-price');
    Route::delete('/events/{event}/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::delete('/events/{event}/photos', [PhotoController::class, 'bulkDestroy'])->name('photos.bulk-destroy');

    // Suporte para Fotógrafos
    Route::prefix('photographer/support')->name('photographer.support.')->group(function () {
        Route::get('/', [PhotographerSupport::class, 'index'])->name('index');
        Route::post('/', [PhotographerSupport::class, 'store'])->name('store');
        Route::get('/{ticket}', [PhotographerSupport::class, 'show'])->name('show');
        Route::post('/{ticket}/message', [PhotographerSupport::class, 'sendMessage'])->name('message');
    });

    // Administração do Suporte (SuperAdmin)
    Route::prefix('admin/support')->name('admin.support.')->group(function () {
        Route::get('/', [AdminSupport::class, 'index'])->name('index');
        Route::get('/{ticket}', [AdminSupport::class, 'show'])->name('show');
        Route::post('/{ticket}/message', [AdminSupport::class, 'sendMessage'])->name('message');
        Route::patch('/{ticket}/toggle', [AdminSupport::class, 'toggleStatus'])->name('toggle');
    });
});

require __DIR__.'/auth.php';
