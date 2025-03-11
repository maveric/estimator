<?php

use App\Http\Controllers\TestDataController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Items\ItemForm;
use App\Livewire\Assemblies\AssemblyList;
use App\Livewire\Assemblies\AssemblyForm;

// Debug routes - outside middleware groups
Route::get('/debug-routes', function() {
    dd(Route::getRoutes()->getRoutesByName());
});

Livewire::setUpdateRoute(function ($handle) { return Route::post('/livewire/update', $handle); });

Route::get('/debug-simple', function() {
    return 'Hello World';
});

Route::get('/assemblies/create-test', AssemblyForm::class)
    ->name('assemblies.create-test')
    ->withoutMiddleware(['auth', 'verified']);


Route::get('/debug-item-form', function() {
    return view('items.form', [
        'mode' => 'create',
        'item' => null
    ])->layout('layouts.app'); // This ensures the app layout is used with Livewire assets
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/items', \App\Livewire\Items\ItemsList::class)->name('items.index');

    // Test route using a regular view with embedded Livewire component
    Route::get('/test-item-form', function () {
        return view('items.test-form');
    })->name('items.test-form');

    // Direct Livewire component route
    Route::get('/items/create', ItemForm::class)->name('items.create');
    
    // Route for editing an item - ensure we're using route model binding
    Route::get('/items/{item}/edit', ItemForm::class)
        ->name('items.edit')
        ->whereNumber('item'); // Ensure item is a number

    // Assembly routes - make sure create comes BEFORE the {assembly} route
    Route::get('/assemblies', AssemblyList::class)->name('assemblies.index');
    Route::get('/assemblies/create', AssemblyForm::class)->name('assemblies.create');
    
    // Update the edit route to use route model binding
    Route::get('/assemblies/{assembly}/edit', AssemblyForm::class)
        ->name('assemblies.edit')
        ->whereNumber('assembly'); // Ensure assembly is a number
    
    // Settings route
    Route::get('/settings', \App\Livewire\Settings\SettingsManager::class)->name('settings.index');
    
    // Optional: Add a route for viewing individual assemblies if needed
    // Route::get('/assemblies/{assembly}', AssemblyShow::class)->name('assemblies.show');

    Route::get('/categories', \App\Livewire\Categories\CategoryManager::class)->name('categories.index');

    // Estimates Routes
    Route::get('/estimates', \App\Livewire\Estimates\ListEstimates::class)->name('estimates.index');
    Route::get('/estimates/create', \App\Livewire\Estimates\EstimateForm::class)->name('estimates.create');
    Route::get('/estimates/{estimate}/edit', \App\Livewire\Estimates\EstimateForm::class)
        ->name('estimates.edit')
        ->whereNumber('estimate');
    Route::get('/estimates/{estimate}', App\Livewire\Estimates\EstimateView::class)
        ->name('estimates.view')
        ->whereNumber('estimate');

    // Packages routes
    Route::prefix('packages')->group(function () {
        Route::get('/', function () {
            return view('packages.index');
        })->name('packages.index');
        
        Route::get('/create', function () {
            return view('packages.create');
        })->name('packages.create');
        
        Route::get('/{package}', function (\App\Models\Package $package) {
            return view('packages.show', ['package' => $package]);
        })->name('packages.show');
        
        Route::get('/{package}/edit', function (\App\Models\Package $package) {
            return view('packages.edit', ['package' => $package]);
        })->name('packages.edit');
    })->middleware('tenant.access');
});

// Tenant selection routes - moved outside the nested middleware group
Route::get('/tenants/select', [\App\Http\Controllers\TenantController::class, 'select'])
    ->name('tenants.select')
    ->middleware(['auth', 'admin']);
Route::post('/tenants/set-current', [\App\Http\Controllers\TenantController::class, 'setCurrent'])
    ->name('tenants.set-current')
    ->middleware(['auth', 'admin']);

Route::get('/debug-full', function() {
    dd([
        'routes' => collect(Route::getRoutes())->map(function($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        })->toArray(),
        'livewire_component_paths' => [
            'expected_file' => app_path('Livewire/Items/ItemForm.php'),
            'file_exists' => file_exists(app_path('Livewire/Items/ItemForm.php')),
            'class_exists' => class_exists(\App\Livewire\Items\ItemForm::class),
        ],
        'view_paths' => [
            'expected_view' => resource_path('views/livewire/items/item-form.blade.php'),
            'view_exists' => view()->exists('livewire.items.item-form'),
            'file_exists' => file_exists(resource_path('views/livewire/items/item-form.blade.php')),
        ],
        'app_config' => [
            'providers' => array_keys(app()->getLoadedProviders()),
            'livewire_config' => config('livewire'),
        ],
        'directory_structure' => [
            'app_livewire' => scandir(app_path('Livewire')),
            'views_livewire' => file_exists(resource_path('views/livewire')) ? scandir(resource_path('views/livewire')) : 'Directory not found',
        ]
    ]);
});

Route::get('/items-test', function() {
    return view('livewire.items.item-form')->layout('layouts.app');
})->name('items.test');

Route::get('/items-simple', function() {
    return "Items page works!";
})->name('items.simple');

Route::get('/test-assembly-create', function() {
    return view('livewire.assemblies.form', [
        'mode' => 'create',
        'assembly' => null,
        'availableItems' => \App\Models\Item::where('is_active', true)->orderBy('name')->get(),
        'items' => [],
        'name' => '',
        'description' => '',
        'is_active' => true,
        'selectedItem' => '',
        'quantity' => 1
    ])->layout('layouts.app');
})->middleware(['auth', 'verified']);

Route::get('/assemblies/simple-test', function() {
    return Livewire::mount('assemblies.assembly-form');
})->withoutMiddleware(['auth', 'verified']);

// Test route for tenant middleware
Route::get('/test-tenant', function() {
    return 'Tenant middleware is working!';
})->middleware(\App\Http\Middleware\EnsureTenantSelected::class);

require __DIR__.'/auth.php';
