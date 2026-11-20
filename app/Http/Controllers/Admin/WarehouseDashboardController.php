<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarehouseLocation;
use Illuminate\View\View;

class WarehouseDashboardController extends Controller
{
    public function show(WarehouseLocation $location): View
    {
        abort_unless($location->is_active && $location->navigation_enabled, 404);

        return view('admin.warehouses.show', compact('location'));
    }
}
