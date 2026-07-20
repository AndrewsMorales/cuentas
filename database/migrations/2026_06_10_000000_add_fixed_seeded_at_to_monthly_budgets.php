<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_budgets', function (Blueprint $table) {
            // Marca el momento en que se materializaron los gastos fijos del
            // mes. Null = aún no sembrados (el mes anterior no ha cerrado).
            $table->timestamp('fixed_seeded_at')->nullable()->after('closed_at');
        });

        // Backfill: todos los presupuestos existentes ya tienen sus gastos
        // fijos cargados, así que se marcan como sembrados para no duplicarlos.
        DB::table('monthly_budgets')
            ->whereNotNull('created_at')
            ->update(['fixed_seeded_at' => DB::raw('created_at')]);

        DB::table('monthly_budgets')
            ->whereNull('fixed_seeded_at')
            ->update(['fixed_seeded_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('monthly_budgets', function (Blueprint $table) {
            $table->dropColumn('fixed_seeded_at');
        });
    }
};
