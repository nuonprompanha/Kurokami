<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Manhwa;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'manhwas' => Manhwa::query()->count(),
            'chapters' => Chapter::query()->count(),
            'views' => Manhwa::query()->sum('views'),
            'users' => User::query()->count(),
        ];

        return view('admin.dashboard.index', compact('stats'));
    }
}
