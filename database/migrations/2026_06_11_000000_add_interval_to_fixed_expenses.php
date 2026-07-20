<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_expenses', function (Blueprint $table) {
            // Frecuencia del gasto fijo: cada cuántos meses se carga.
            // 1 = todos los meses (comportamiento por defecto).
            $table->unsignedTinyInteger('interval_months')->default(1)->after('fortnight');

            // Mes de referencia (un mes en que SÍ aplica). Sirve de ancla para
            // calcular en qué meses futuros recae cuando interval_months > 1.
            $table->unsignedSmallInteger('anchor_year')->nullable()->after('interval_months');
            $table->unsignedTinyInteger('anchor_month')->nullable()->after('anchor_year');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_expenses', function (Blueprint $table) {
            $table->dropColumn(['interval_months', 'anchor_year', 'anchor_month']);
        });
    }
};
