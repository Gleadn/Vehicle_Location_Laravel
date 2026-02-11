<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationDemandController;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function (VehicleService $vehicleService) {
    $vehicles = $vehicleService->getCheapestVehicles(8);
    
    return view('welcome', compact('vehicles'));
})->name('home');

Route::get('/vehicles', function (VehicleService $vehicleService) {
    $vehiclesByType = [
        'car' => Vehicle::where('type', 'car')->get(),
        'motorcycle' => Vehicle::where('type', 'motorcycle')->get(),
        'van' => Vehicle::where('type', 'van')->get(),
        'sport' => Vehicle::where('type', 'sport')->get(),
    ];
    
    return view('vehicles', compact('vehiclesByType'));
})->name('vehicles');

Route::get('/locationDemand', function () {
    return view('location-demand');
})->name('locationDemand');

// Routes pour les demandes de location (nécessite d'être connecté)
Route::middleware('auth')->group(function () {
    Route::post('/location-demands', [LocationDemandController::class, 'store'])->name('location-demands.store');
    Route::post('/location-proposals/{proposal}/accept', [LocationDemandController::class, 'acceptProposal'])->name('location-proposals.accept');
    Route::post('/vehicles/reserve', [LocationDemandController::class, 'reserveVehicle'])->name('vehicles.reserve');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        $locations = Auth::user()->locations()
            ->with('vehicle')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('profile', compact('locations'));
    })->name('profile');
    
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::delete('/account', [AuthController::class, 'deleteAccount'])->name('account.delete');
});

// Routes admin (nécessite le rôle admin)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});
