<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('global_income_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_direct_ref_invest', 16, 8)->default(0);
            $table->decimal('min_team_invest', 16, 8)->default(0);
            $table->decimal('roi_percentage', 5, 2)->default(2.00);
            $table->timestamps();
        });

        DB::table('global_income_settings')->insert([
            'min_direct_ref_invest' => 1000.00000000,
            'min_team_invest' => 5000.00000000,
            'roi_percentage' => 2.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_income_settings');
    }
};
