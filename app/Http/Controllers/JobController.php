<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Deal::whereNotNull('job_number');

        // Access Control
        $user = auth()->user();
        if (!in_array($user->role, ['Super Admin', 'Management'])) {
            $query->where(function ($q) use ($user) {
                // Own jobs
                $q->where('user_id', $user->id)
                  // Team member jobs
                  ->orWhereHas('teamMembers', function ($tm) use ($user) {
                      $tm->where('users.id', $user->id);
                  });
                
                // HOD specific: Department split and subordinates
                if ($user->role === 'HOD') {
                    if ($user->department) {
                        $q->orWhere('department_split', 'like', '%' . $user->department . '%');
                    }
                    
                    // Jobs owned by subordinates
                    $subordinateIds = \App\Models\User::where('supervisor_id', $user->id)->pluck('id');
                    if ($subordinateIds->isNotEmpty()) {
                        $q->orWhereIn('user_id', $subordinateIds);
                    }
                }
            });
        }

        // Filters
        // 1. Date Range (created_at)
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 2. Department (Check if department_split JSON contains the department)
        if ($request->has('department')) {
            $filterDepts = array_filter((array)$request->input('department'));
            if (!empty($filterDepts)) {
                $query->where(function ($q) use ($filterDepts) {
                    foreach ($filterDepts as $dept) {
                        $q->orWhereJsonContains('department_split', [['department' => $dept]]);
                    }
                });
            }
        }

        // 3. User (Deal Owner)
        if ($request->has('user_id')) {
            $filterUsers = array_filter((array)$request->input('user_id'));
            if (!empty($filterUsers)) {
                $query->whereIn('user_id', $filterUsers);
            }
        }

        $jobs = $query->orderBy('job_number', 'desc')->get();

        // Data for dropdowns
        if (in_array($user->role, ['Super Admin', 'Management'])) {
            $users = \App\Models\User::all();
        } elseif ($user->role === 'HOD') {
            $users = \App\Models\User::where('id', $user->id)
                ->orWhere('supervisor_id', $user->id)
                ->get();
        } else {
            $users = \App\Models\User::where('id', $user->id)->get();
        }
        $departments = ['Creative', 'Digital', 'Play', 'Tech', 'PM', 'Corporate']; // Default departments

        return view('jobs.index', compact('jobs', 'users', 'departments'));
    }
}
