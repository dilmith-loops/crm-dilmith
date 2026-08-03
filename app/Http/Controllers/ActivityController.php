<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::with('user')
            ->latest()
            ->paginate(50);

        return view('activities.index', compact('activities'));
    }
}
