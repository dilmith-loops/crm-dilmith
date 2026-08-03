<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected function logAction($description, $subject = null, $properties = [])
    {
        Activity::create([
            'user_id' => Auth::id(),
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }
}
