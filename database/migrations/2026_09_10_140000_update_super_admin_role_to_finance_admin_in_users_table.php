<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'Super Admin')
            ->orWhere('role', 'super_admin')
            ->update(['role' => 'Finance Admin']);

        // Update historical notifications
        if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('data', 'like', '%Super Admin%')
                ->get()
                ->each(function ($n) {
                    $newData = str_replace('Super Admin', 'Finance Admin', $n->data);
                    DB::table('notifications')->where('id', $n->id)->update(['data' => $newData]);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('role', 'Finance Admin')
            ->update(['role' => 'Super Admin']);
    }
};
