<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return response()->json(Role::withCount('users')->get());
    }
}
