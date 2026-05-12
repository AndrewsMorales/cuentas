<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Datos de demostración.
 *
 * Un hogar de ejemplo con tres meses cerrados: dos personas, nueve categorías,
 * gastos fijos, ingresos por quincena y movimientos suficientes para que el
 * resumen tenga algo que mostrar apenas se ejecuta `php artisan migrate --seed`.
 *
 * Las cifras son inventadas y redondas a propósito: esto es para probar la
 * aplicación, no la contabilidad de nadie.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();
            $pw = Hash::make('demo1234');

            // ─── Usuarios ─────────────────────────────────────────────
            User::firstOrCreate(
                ['email' => 'ana@cuentas.test'],
                ['name' => 'Ana', 'role' => User::ROLE_MANAGER,
                 'is_super_admin' => true, 'password' => $pw],
            );
            User::firstOrCreate(
                ['email' => 'carlos@cuentas.test'],
                ['name' => 'Carlos', 'role' => User::ROLE_MANAGER,
                 'is_super_admin' => false, 'password' => $pw],
            );

            // ─── Personas ─────────────────────────────────────────────
            DB::table('people')->insert($this->stamp([
                ['id' => 1, 'name' => 'Ana', 'color' => '#d63384'],
                ['id' => 2, 'name' => 'Carlos', 'color' => '#0d6efd'],
            ], $now));

            // ─── Categorías ───────────────────────────────────────────
            DB::table('categories')->insert($this->stamp([
                ['id' => 1, 'name' => 'Hogar', 'icon' => 'bi-house-door', 'color' => '#0d6efd'],
                ['id' => 2, 'name' => 'Alimentación', 'icon' => 'bi-basket', 'color' => '#198754'],
                ['id' => 3, 'name' => 'Salidas', 'icon' => 'bi-cup-straw', 'color' => '#fd7e14'],
                ['id' => 4, 'name' => 'Transporte', 'icon' => 'bi-bicycle', 'color' => '#6f42c1'],
                ['id' => 5, 'name' => 'Servicios', 'icon' => 'bi-lightning', 'color' => '#ffc107'],
                ['id' => 6, 'name' => 'Salud', 'icon' => 'bi-heart-pulse', 'color' => '#dc3545'],
                ['id' => 7, 'name' => 'Deudas', 'icon' => 'bi-credit-card', 'color' => '#6c757d'],
                ['id' => 8, 'name' => 'Ahorro', 'icon' => 'bi-piggy-bank', 'color' => '#20c997'],
                ['id' => 9, 'name' => 'Varios', 'icon' => 'bi-three-dots', 'color' => '#adb5bd'],
            ], $now));

            // ─── Plantillas de gastos fijos ──────────────────────────
            // Se materializan solas al abrir cada mes nuevo.
            DB::table('fixed_expenses')->insert($this->stamp([
                ['id' => 1, 'category_id' => 1, 'name' => 'Arriendo', 'average_amount' => 900000, 'fortnight' => 2, 'active' => true],
                ['id' => 2, 'category_id' => 1, 'name' => 'Administración', 'average_amount' => 150000, 'fortnight' => 1, 'active' => true],
                ['id' => 3, 'category_id' => 5, 'name' => 'Internet', 'average_amount' => 90000, 'fortnight' => 1, 'active' => true],
                ['id' => 4, 'category_id' => 5, 'name' => 'Energía', 'average_amount' => 70000, 'fortnight' => 2, 'active' => true],
                ['id' => 5, 'category_id' => 4, 'name' => 'Parqueadero', 'average_amount' => 80000, 'fortnight' => 1, 'active' => true],
                ['id' => 6, 'category_id' => 6, 'name' => 'Medicina prepagada', 'average_amount' => 120000, 'fortnight' => 2, 'active' => true],
            ], $now));

            // ─── Meses ────────────────────────────────────────────────
            DB::table('monthly_budgets')->insert($this->stamp([
                ['id' => 1, 'year' => 2026, 'month' => 5],
                ['id' => 2, 'year' => 2026, 'month' => 6],
                ['id' => 3, 'year' => 2026, 'month' => 7],
            ], $now));

            // ─── Ingresos por quincena ────────────────────────────────
            DB::table('incomes')->insert($this->stamp([
                ['monthly_budget_id' => 1, 'person_id' => 1, 'amount' => 1600000, 'fortnight' => 1, 'received_at' => '2026-05-15 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 1, 'person_id' => 1, 'amount' => 1600000, 'fortnight' => 2, 'received_at' => '2026-05-30 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 1, 'person_id' => 2, 'amount' => 1400000, 'fortnight' => 1, 'received_at' => '2026-05-15 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 1, 'person_id' => 2, 'amount' => 1400000, 'fortnight' => 2, 'received_at' => '2026-05-30 00:00:00', 'note' => 'Salario'],

                ['monthly_budget_id' => 2, 'person_id' => 1, 'amount' => 1600000, 'fortnight' => 1, 'received_at' => '2026-06-15 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 2, 'person_id' => 1, 'amount' => 1600000, 'fortnight' => 2, 'received_at' => '2026-06-30 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 2, 'person_id' => 2, 'amount' => 1400000, 'fortnight' => 1, 'received_at' => '2026-06-15 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 2, 'person_id' => 2, 'amount' => 1400000, 'fortnight' => 2, 'received_at' => '2026-06-30 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 2, 'person_id' => 2, 'amount' => 350000, 'fortnight' => 2, 'received_at' => '2026-06-28 00:00:00', 'note' => 'Trabajo extra'],

                ['monthly_budget_id' => 3, 'person_id' => 1, 'amount' => 1600000, 'fortnight' => 1, 'received_at' => '2026-07-15 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 3, 'person_id' => 1, 'amount' => 1600000, 'fortnight' => 2, 'received_at' => '2026-07-30 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 3, 'person_id' => 2, 'amount' => 1400000, 'fortnight' => 1, 'received_at' => '2026-07-15 00:00:00', 'note' => 'Salario'],
                ['monthly_budget_id' => 3, 'person_id' => 2, 'amount' => 1400000, 'fortnight' => 2, 'received_at' => '2026-07-30 00:00:00', 'note' => 'Salario'],
            ], $now));

            // ─── Gastos ───────────────────────────────────────────────
            DB::table('expenses')->insert($this->stamp([
                // Mayo
                ['monthly_budget_id' => 1, 'category_id' => 1, 'person_id' => 1, 'fixed_expense_id' => 2, 'description' => 'Administración', 'amount' => 150000, 'spent_at' => '2026-05-05 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 5, 'person_id' => 2, 'fixed_expense_id' => 3, 'description' => 'Internet', 'amount' => 90000, 'spent_at' => '2026-05-07 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 4, 'person_id' => 2, 'fixed_expense_id' => 5, 'description' => 'Parqueadero', 'amount' => 80000, 'spent_at' => '2026-05-08 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 2, 'person_id' => 1, 'fixed_expense_id' => null, 'description' => 'Mercado quincena', 'amount' => 380000, 'spent_at' => '2026-05-10 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 3, 'person_id' => 2, 'fixed_expense_id' => null, 'description' => 'Cine', 'amount' => 48000, 'spent_at' => '2026-05-12 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 1, 'person_id' => 1, 'fixed_expense_id' => 1, 'description' => 'Arriendo', 'amount' => 900000, 'spent_at' => '2026-05-25 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 5, 'person_id' => 1, 'fixed_expense_id' => 4, 'description' => 'Energía', 'amount' => 68000, 'spent_at' => '2026-05-26 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 6, 'person_id' => 2, 'fixed_expense_id' => 6, 'description' => 'Medicina prepagada', 'amount' => 120000, 'spent_at' => '2026-05-27 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 2, 'person_id' => 2, 'fixed_expense_id' => null, 'description' => 'Mercado quincena', 'amount' => 340000, 'spent_at' => '2026-05-28 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 1, 'category_id' => 8, 'person_id' => 1, 'fixed_expense_id' => null, 'description' => 'Ahorro del mes', 'amount' => 400000, 'spent_at' => '2026-05-30 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],

                // Junio
                ['monthly_budget_id' => 2, 'category_id' => 1, 'person_id' => 1, 'fixed_expense_id' => 2, 'description' => 'Administración', 'amount' => 150000, 'spent_at' => '2026-06-05 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 5, 'person_id' => 2, 'fixed_expense_id' => 3, 'description' => 'Internet', 'amount' => 90000, 'spent_at' => '2026-06-07 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 4, 'person_id' => 2, 'fixed_expense_id' => 5, 'description' => 'Parqueadero', 'amount' => 80000, 'spent_at' => '2026-06-08 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 2, 'person_id' => 1, 'fixed_expense_id' => null, 'description' => 'Mercado quincena', 'amount' => 410000, 'spent_at' => '2026-06-11 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 7, 'person_id' => 2, 'fixed_expense_id' => null, 'description' => 'Cuota tarjeta', 'amount' => 260000, 'spent_at' => '2026-06-12 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 1, 'person_id' => 1, 'fixed_expense_id' => 1, 'description' => 'Arriendo', 'amount' => 900000, 'spent_at' => '2026-06-25 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 5, 'person_id' => 1, 'fixed_expense_id' => 4, 'description' => 'Energía', 'amount' => 74000, 'spent_at' => '2026-06-26 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 6, 'person_id' => 2, 'fixed_expense_id' => 6, 'description' => 'Medicina prepagada', 'amount' => 120000, 'spent_at' => '2026-06-27 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 3, 'person_id' => 1, 'fixed_expense_id' => null, 'description' => 'Cumpleaños', 'amount' => 190000, 'spent_at' => '2026-06-28 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 2, 'category_id' => 8, 'person_id' => 2, 'fixed_expense_id' => null, 'description' => 'Ahorro del mes', 'amount' => 500000, 'spent_at' => '2026-06-30 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],

                // Julio
                ['monthly_budget_id' => 3, 'category_id' => 1, 'person_id' => 1, 'fixed_expense_id' => 2, 'description' => 'Administración', 'amount' => 150000, 'spent_at' => '2026-07-05 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 5, 'person_id' => 2, 'fixed_expense_id' => 3, 'description' => 'Internet', 'amount' => 90000, 'spent_at' => '2026-07-07 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 4, 'person_id' => 2, 'fixed_expense_id' => 5, 'description' => 'Parqueadero', 'amount' => 80000, 'spent_at' => '2026-07-08 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 2, 'person_id' => 1, 'fixed_expense_id' => null, 'description' => 'Mercado quincena', 'amount' => 395000, 'spent_at' => '2026-07-10 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 6, 'person_id' => 1, 'fixed_expense_id' => null, 'description' => 'Odontología', 'amount' => 210000, 'spent_at' => '2026-07-14 00:00:00', 'fortnight' => 1, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 1, 'person_id' => 1, 'fixed_expense_id' => 1, 'description' => 'Arriendo', 'amount' => 900000, 'spent_at' => '2026-07-25 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 5, 'person_id' => 1, 'fixed_expense_id' => 4, 'description' => 'Energía', 'amount' => 71000, 'spent_at' => '2026-07-26 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 6, 'person_id' => 2, 'fixed_expense_id' => 6, 'description' => 'Medicina prepagada', 'amount' => 120000, 'spent_at' => '2026-07-27 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 2, 'person_id' => 2, 'fixed_expense_id' => null, 'description' => 'Mercado quincena', 'amount' => 360000, 'spent_at' => '2026-07-28 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
                ['monthly_budget_id' => 3, 'category_id' => 9, 'person_id' => 2, 'fixed_expense_id' => null, 'description' => 'Varios', 'amount' => 85000, 'spent_at' => '2026-07-29 00:00:00', 'fortnight' => 2, 'is_fixed_template' => false],
            ], $now));
        });
    }

    /** Adjunta timestamps a cada fila para bulk insert. */
    private function stamp(array $rows, Carbon $at): array
    {
        return array_map(fn ($r) => $r + ['created_at' => $at, 'updated_at' => $at], $rows);
    }
}
