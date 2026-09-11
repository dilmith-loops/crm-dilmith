<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $iouCat = DB::table('expense_categories')->where('name', 'IOU')->first();
        if ($iouCat) {
            // Unlink any items that were referencing the IOU category
            DB::table('petty_cash_items')
                ->where('expense_category_id', $iouCat->id)
                ->update(['expense_category_id' => null]);

            // Delete the IOU category from expense categories table
            DB::table('expense_categories')
                ->where('id', $iouCat->id)
                ->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $exists = DB::table('expense_categories')->where('name', 'IOU')->exists();
        if (!$exists) {
            DB::table('expense_categories')->insert([
                'name' => 'IOU',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
