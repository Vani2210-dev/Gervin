<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AiapplicationController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\ComponentspageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CryptocurrencyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ManufactureController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ManufactureStepController;

Route::middleware(['auth'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('index');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::controller(HomeController::class)->group(function () {
    Route::get('calendar-Main','calendarMain')->name('calendarMain');
    Route::get('chatempty','chatempty')->name('chatempty');
    Route::get('chat-message','chatMessage')->name('chatMessage');
    Route::get('chat-profile','chatProfile')->name('chatProfile');
    Route::get('email','email')->name('email');
    Route::get('faq','faq')->name('faq');
    Route::get('gallery','gallery')->name('gallery');
    Route::get('image-upload','imageUpload')->name('imageUpload');
    Route::get('kanban','kanban')->name('kanban');
    Route::get('page-error','pageError')->name('pageError');
    Route::get('pricing','pricing')->name('pricing');
    Route::get('starred','starred')->name('starred');
    Route::get('terms-condition','termsCondition')->name('termsCondition');
    Route::get('veiw-details','veiwDetails')->name('veiwDetails');
    Route::get('widgets','widgets')->name('widgets');
});

// aiApplication
Route::prefix('aiapplication')->group(function () {
    Route::controller(AiapplicationController::class)->group(function () {
        Route::get('/code-generator', 'codeGenerator')->name('codeGenerator');
        Route::get('/code-generatornew', 'codeGeneratorNew')->name('codeGeneratorNew');
        Route::get('/image-generator','imageGenerator')->name('imageGenerator');
        Route::get('/text-generator','textGenerator')->name('textGenerator');
        Route::get('/text-generatornew','textGeneratorNew')->name('textGeneratorNew');
        Route::get('/video-generator','videoGenerator')->name('videoGenerator');
        Route::get('/voice-generator','voiceGenerator')->name('voiceGenerator');
    });
});

// Authentication (legacy redirects to Breeze routes)
Route::prefix('authentication')->group(function () {
    Route::get('/sign-in', function () {
        return redirect()->route('login');
    })->name('signin');
    Route::get('/sign-up', function () {
        return redirect()->route('register');
    })->name('signup');
    Route::get('/forgot-password', function () {
        return redirect()->route('password.request');
    })->name('forgotPassword');
});

// chart
Route::prefix('chart')->group(function () {
    Route::controller(ChartController::class)->group(function () {
        Route::get('/column-chart', 'columnChart')->name('columnChart');
        Route::get('/line-chart', 'lineChart')->name('lineChart');
        Route::get('/pie-chart', 'pieChart')->name('pieChart');
    });
});

// Componentpage
Route::prefix('componentspage')->group(function () {
    Route::controller(ComponentspageController::class)->group(function () {
        Route::get('/alert', 'alert')->name('alert');
        Route::get('/avatar', 'avatar')->name('avatar');
        Route::get('/badges', 'badges')->name('badges');
        Route::get('/button', 'button')->name('button');
        Route::get('/calendar', 'calendar')->name('calendar');
        Route::get('/card', 'card')->name('card');
        Route::get('/carousel', 'carousel')->name('carousel');
        Route::get('/colors', 'colors')->name('colors');
        Route::get('/dropdown', 'dropdown')->name('dropdown');
        Route::get('/imageupload', 'imageUpload')->name('imageUpload');
        Route::get('/list', 'list')->name('list');
        Route::get('/pagination', 'pagination')->name('pagination');
        Route::get('/progress', 'progress')->name('progress');
        Route::get('/radio', 'radio')->name('radio');
        Route::get('/star-rating', 'starRating')->name('starRating');
        Route::get('/switch', 'switch')->name('switch');
        Route::get('/tabs', 'tabs')->name('tabs');
        Route::get('/tags', 'tags')->name('tags');
        Route::get('/tooltip', 'tooltip')->name('tooltip');
        Route::get('/typography', 'typography')->name('typography');
        Route::get('/videos', 'videos')->name('videos');
    });
});

// Cryptocurrency
Route::prefix('cryptocurrency')->group(function () {
    Route::controller(CryptocurrencyController::class)->group(function () {
        Route::get('/wallet','wallet')->name('wallet');
    });
});

// Dashboard
Route::prefix('dashboard')->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::get('/index-2', 'index2')->name('index2');
        Route::get('/index-3', 'index3')->name('index3');
        Route::get('/index-4', 'index4')->name('index4');
        Route::get('/index-5','index5')->name('index5');
        Route::get('/index-6','index6')->name('index6');
        Route::get('/index-7','index7')->name('index7');
        Route::get('/index-8','index8')->name('index8');
        Route::get('/index-9','index9')->name('index9');
    });
});

// Forms
Route::prefix('forms')->group(function () {
    Route::controller(FormsController::class)->group(function () {
        Route::get('/form', 'form')->name('form');
        Route::get('/form-layout', 'formLayout')->name('formLayout');
        Route::get('/form-validation', 'formValidation')->name('formValidation');
        Route::get('/wizard', 'wizard')->name('wizard');
    });
});

// Invoice
Route::prefix('invoice')->group(function () {
    Route::controller(InvoiceController::class)->group(function () {
        Route::get('/invoice-add', 'invoiceAdd')->name('invoiceAdd');
        Route::get('/invoice-edit', 'invoiceEdit')->name('invoiceEdit');
        Route::get('/invoice-list', 'invoiceList')->name('invoiceList');
        Route::get('/invoice-preview', 'invoicePreview')->name('invoicePreview');
    });
});

// Settings
Route::prefix('settings')->group(function () {
    Route::controller(SettingsController::class)->group(function () {
        Route::get('/company', 'company')->name('company');
        Route::get('/currencies', 'currencies')->name('currencies');
        Route::get('/language', 'language')->name('language');
        Route::get('/notification', 'notification')->name('notification');
        Route::get('/notification-alert', 'notificationAlert')->name('notificationAlert');
        Route::get('/payment-gateway', 'paymentGateway')->name('paymentGateway');
        Route::get('/theme', 'theme')->name('theme');
    });
});

// Table
Route::prefix('table')->group(function () { 
    Route::controller(TableController::class)->group(function () {
        Route::get('/table-basic', 'tableBasic')->name('tableBasic');
        Route::get('/table-data', 'tableData')->name('tableData');
    });
});

// Users
Route::middleware(['auth'])->prefix('users')->group(function () {
    Route::controller(UsersController::class)->group(function () {
        Route::get('/', 'index')->name('users.index');
        Route::get('/add', 'create')->name('users.create');
        Route::post('/', 'store')->name('users.store');
        Route::get('/users-grid', 'usersGrid')->name('usersGrid');
        Route::get('/view-profile', 'viewProfile')->name('viewProfile');
        Route::post('/view-profile', 'updateProfile')->name('updateProfile');
        Route::get('/{user}/edit', 'edit')->name('users.edit');
        Route::put('/{user}', 'update')->name('users.update');
        Route::delete('/{user}', 'destroy')->name('users.destroy');
        Route::get('/{user}/avatar', 'avatar')->name('users.avatar');
        Route::get('/{user}', 'show')->name('users.show');
    });
});

// Roles
Route::middleware(['auth'])->resource('roles', RoleController::class);

// Media
Route::middleware(['auth'])->prefix('media')->name('media.')->group(function () {
    Route::get('/', [MediaController::class, 'index'])->name('index');
    Route::post('/', [MediaController::class, 'store'])->name('store');
    Route::post('/folder', [MediaController::class, 'createFolder'])->name('folder');
    Route::delete('/{filename}', [MediaController::class, 'destroy'])->name('destroy')->where('filename', '.*');
    Route::get('/serve/{filename}', [MediaController::class, 'serve'])->name('serve')->where('filename', '.*');
    Route::get('/download/{filename}', [MediaController::class, 'download'])->name('download')->where('filename', '.*');
});


// Supplies
Route::middleware(['auth'])->group(function () {
    Route::resource('supplies', SupplyController::class)->names('supplies');
});

// Warehouses
Route::middleware(['auth'])->group(function () {
    Route::resource('warehouses', WarehouseController::class)->names('warehouses');
    Route::post('warehouses/{warehouse}/config', [WarehouseController::class, 'updateConfig'])->name('warehouses.config.update');
    Route::post('warehouses/{warehouse}/records', [WarehouseController::class, 'storeRecord'])->name('warehouses.records.store');
    Route::put('warehouses/{warehouse}/records/{record}', [WarehouseController::class, 'updateRecord'])->name('warehouses.records.update');
    Route::delete('warehouses/{warehouse}/records/{record}', [WarehouseController::class, 'destroyRecord'])->name('warehouses.records.destroy');
    Route::get('warehouses/{warehouse}/export-template', [WarehouseController::class, 'exportTemplate'])->name('warehouses.export-template');
    Route::get('warehouses/{warehouse}/export-data', [WarehouseController::class, 'exportData'])->name('warehouses.export-data');
    Route::post('warehouses/{warehouse}/import', [WarehouseController::class, 'import'])->name('warehouses.import');
    Route::post('warehouses/{warehouse}/import-json', [WarehouseController::class, 'importJson'])->name('warehouses.import-json');
    Route::get('warehouses/{warehouse}/records/{record}/print', [WarehouseController::class, 'printRecordVoucher'])->name('warehouses.records.print');
});

// Customers
Route::middleware(['auth'])->group(function () {
    Route::resource('customers', CustomerController::class)->names('customers');
});

// Orders
Route::middleware(['auth'])->group(function () {
    Route::resource('orders', OrderController::class)->names('orders');
    Route::get('orders/create/{type}', [OrderController::class, 'createByType'])->name('orders.create.type');
    Route::get('orders/image/{filename}', [OrderController::class, 'serveImage'])->name('orders.image');
});

// Manufacture Orders
Route::middleware(['auth'])->group(function () {
    Route::resource('manufactures', ManufactureController::class)->names('manufactures');
    Route::post('manufactures/{manufacture}/approve/{step}', [ManufactureController::class, 'approveStep'])->name('manufactures.approve');
    Route::get('manufactures/{manufacture}/print-stamps', [ManufactureController::class, 'printStamps'])->name('manufactures.print-stamps');
    Route::get('manufactures/qr/{product_code}', [ManufactureController::class, 'serveQrCode'])->name('manufactures.qr');
});

// Manufacture Steps / Processes
Route::middleware(['auth'])->prefix('processes')->name('processes.')->group(function () {
    Route::get('/cnc', [ManufactureStepController::class, 'cnc'])->name('cnc');
    Route::post('/cnc/complete', [ManufactureStepController::class, 'completeCnc'])->name('cnc.complete');
    Route::get('/pressing', [ManufactureStepController::class, 'pressing'])->name('pressing');
    Route::get('/edge-banding', [ManufactureStepController::class, 'edgeBanding'])->name('edge-banding');
    Route::post('/edge-banding/complete', [ManufactureStepController::class, 'completeEdgeBanding'])->name('edge-banding.complete');
    Route::get('/finishing', [ManufactureStepController::class, 'finishing'])->name('finishing');
    Route::get('/qc', [ManufactureStepController::class, 'qc'])->name('qc');
    Route::get('/packing', [ManufactureStepController::class, 'packing'])->name('packing');
    Route::get('/shipped', [ManufactureStepController::class, 'shipped'])->name('shipped');
});

require __DIR__.'/auth.php';
