<?php

namespace App\Traits;

use App\Models\Deal;
use App\Models\User;
use App\Notifications\DealUpdatedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait NotifiesStakeholders
{
    protected function notifyStakeholders(Deal $deal, $notification = null)
    {
        $user = Auth::user();
        \Illuminate\Support\Facades\Log::info("Notifying stakeholders for deal: {$deal->title} by user: " . ($user->name ?? 'Unknown'));

        $stakeholders = collect();

        // 1. Deal Owner and their HOD
        if ($deal->owner) {
            $stakeholders->push($deal->owner);
            
            // Find HODs in the owner's department
            if ($deal->owner->department) {
                $hods = User::where('department', $deal->owner->department)
                    ->where('role', 'HOD')
                    ->get();
                $stakeholders = $stakeholders->merge($hods);
            }
        }

        // 2. Project Split Users (Users in departments listed in department_split)
        $splits = is_string($deal->department_split) ? json_decode($deal->department_split, true) : $deal->department_split;
        if (is_array($splits)) {
            foreach ($splits as $split) {
                $dept = $split['department'] ?? null;
                if ($dept) {
                    $deptUsers = User::where('department', $dept)->get();
                    $stakeholders = $stakeholders->merge($deptUsers);
                }
            }
        }

        // 3. Team Members
        $stakeholders = $stakeholders->merge($deal->teamMembers);

        // Filter out the person who made the change and duplicates
        $stakeholders = $stakeholders->reject(function ($s) use ($user) {
            return $s->id === $user->id;
        })->unique('id');

        \Illuminate\Support\Facades\Log::info("Found " . $stakeholders->count() . " stakeholders to notify.");

        $notification = $notification ?? new DealUpdatedNotification($deal, $user);

        foreach ($stakeholders as $stakeholder) {
            $stakeholder->notify($notification);
        }
    }
}
