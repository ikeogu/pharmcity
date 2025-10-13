<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\Patient;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 🧾 Summary Counts
        $totalDrugs = Drug::count();
        $totalPatients = Patient::count();
        $totalUsers = User::count();
        $totalSales = Sale::completed()->sum('total');

        // 📊 Weekly Sales Data
        $weeklySales =  Sale::completed()->selectRaw("
                strftime('%w', created_at) as weekday,
                SUM(total) as total
            ")
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->groupBy('weekday')
            ->orderBy('weekday')
            ->get()
            ->map(function ($row) {
                // Convert numeric weekday to readable label
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return [
                    'day' => $days[$row->weekday],
                    'total' => $row->total,
                ];
            });


        // 🧾 Recent Sales (last 5)
        $recentSales = Sale::completed()->latest()->take(5)->get(['id', 'invoice_number', 'total', 'status']);

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'totalDrugs' => $totalDrugs,
                'totalPatients' => $totalPatients,
                'totalUsers' => $totalUsers,
                'totalSales' => $totalSales,
            ],
            'weeklySales' => $weeklySales,
            'recentSales' => $recentSales,
        ]);
    }
}
