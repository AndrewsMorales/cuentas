<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->unsignedTinyInteger('fortnight');
            $table->date('received_at');
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
