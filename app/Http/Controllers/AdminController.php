<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Location;
use App\Models\LocationDemand;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Afficher le panel d'administration
     */
    public function index()
    {
        // Statistiques générales
        $stats = [
            'users' => [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'regular' => User::where('role', 'user')->count(),
                'recent' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'vehicles' => [
                'total' => Vehicle::count(),
                'available' => Vehicle::where('available', true)->where('status', 'active')->count(),
                'maintenance' => Vehicle::where('status', 'maintenance')->count(),
                'unavailable' => Vehicle::where('available', false)->count(),
            ],
            'locations' => [
                'total' => Location::count(),
                'confirmed' => Location::where('status', 'confirmed')->count(),
                'active' => Location::where('status', 'active')->count(),
                'completed' => Location::where('status', 'completed')->count(),
            ],
            'demands' => [
                'total' => LocationDemand::count(),
                'pending' => LocationDemand::where('status', 'pending')->count(),
                'processing' => LocationDemand::where('status', 'processing')->count(),
                'proposed' => LocationDemand::where('status', 'proposed')->count(),
            ],
        ];

        // Dernières réservations
        $recentLocations = Location::with(['user', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        // Derniers utilisateurs
        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        // Derniers vehicules
        $recentVehicles = Vehicle::orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        // Dernieres demandes
        $recentDemands = LocationDemand::with(['user', 'requestedVehicle'])
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        return view('admin.index', compact('stats', 'recentLocations', 'recentUsers', 'recentVehicles', 'recentDemands'));
    }
}
