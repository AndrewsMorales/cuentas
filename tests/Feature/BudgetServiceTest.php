<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\FixedExpense;
use App\Models\Income;
use App\Models\MonthlyBudget;
use App\Models\Person;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * El corazón de la app: resolver el mes, materializar los gastos fijos y
 * calcular el resumen. Es donde más caro sale un error, porque el usuario
 * toma decisiones de plata con esos números.
 */
class BudgetServiceTest extends TestCase
{
    use RefreshDatabase;

    private BudgetService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BudgetService::class);

        // Los gastos fijos solo se siembran pasado el día 5; se fija la fecha
        // para que las pruebas no dependan del día en que se ejecuten.
        Carbon::setTestNow(Carbon::create(2026, 3, 20, 10, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function category(string $name = 'Hogar'): Category
    {
        return Category::create(['name' => $name, 'icon' => 'house', 'color' => '#333']);
    }

    private function person(string $name = 'Andrés'): Person
    {
        return Person::create(['name' => $name]);
    }

    public function test_crea_el_presupuesto_del_mes_si_no_existe(): void
    {
        $budget = $this->service->resolveBudget(2026, 3);

        $this->assertDatabaseHas('monthly_budgets', ['year' => 2026, 'month' => 3]);
        $this->assertSame(2026, $budget->year);
        $this->assertSame(3, $budget->month);
    }

    public function test_no_duplica_el_presupuesto_al_pedirlo_dos_veces(): void
    {
        $first = $this->service->resolveBudget(2026, 3);
        $second = $this->service->resolveBudget(2026, 3);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, MonthlyBudget::count());
    }

    public function test_materializa_los_gastos_fijos_activos_al_abrir_el_mes(): void
    {
        $category = $this->category();

        FixedExpense::create([
            'name' => 'Recibo de luz',
            'category_id' => $category->id,
            'average_amount' => 250000,
            'fortnight' => 1,
            'active' => true,
        ]);

        $budget = $this->service->resolveBudget(2026, 3);

        $this->assertSame(1, $budget->expenses()->count());

        $expense = $budget->expenses()->first();
        $this->assertSame('Recibo de luz', $expense->description);
        $this->assertEqualsWithDelta(250000, (float) $expense->amount, 0.01);
        $this->assertTrue((bool) $expense->is_fixed_template);
        // Queda sin persona a propósito: el usuario decide quién lo asume.
        $this->assertNull($expense->person_id);
    }

    public function test_ignora_los_gastos_fijos_inactivos(): void
    {
        $category = $this->category();

        FixedExpense::create([
            'name' => 'Gimnasio cancelado',
            'category_id' => $category->id,
            'average_amount' => 90000,
            'fortnight' => 1,
            'active' => false,
        ]);

        $budget = $this->service->resolveBudget(2026, 3);

        $this->assertSame(0, $budget->expenses()->count());
    }

    public function test_no_vuelve_a_sembrar_los_fijos_al_reabrir_el_mes(): void
    {
        $category = $this->category();

        FixedExpense::create([
            'name' => 'Internet',
            'category_id' => $category->id,
            'average_amount' => 120000,
            'fortnight' => 1,
            'active' => true,
        ]);

        $this->service->resolveBudget(2026, 3);
        $this->service->resolveBudget(2026, 3);
        $this->service->resolveBudget(2026, 3);

        $this->assertSame(1, Expense::count());
    }

    public function test_ubica_el_gasto_fijo_en_el_dia_de_su_quincena(): void
    {
        $category = $this->category();

        FixedExpense::create([
            'name' => 'Primera quincena',
            'category_id' => $category->id,
            'average_amount' => 1000,
            'fortnight' => 1,
            'active' => true,
        ]);
        FixedExpense::create([
            'name' => 'Segunda quincena',
            'category_id' => $category->id,
            'average_amount' => 2000,
            'fortnight' => 2,
            'active' => true,
        ]);

        $budget = $this->service->resolveBudget(2026, 3);

        $primera = $budget->expenses()->where('description', 'Primera quincena')->first();
        $segunda = $budget->expenses()->where('description', 'Segunda quincena')->first();

        $this->assertSame('2026-03-11', Carbon::parse($primera->spent_at)->toDateString());
        $this->assertSame('2026-03-26', Carbon::parse($segunda->spent_at)->toDateString());
    }

    public function test_omite_un_gasto_bimestral_en_el_mes_que_no_le_toca(): void
    {
        $category = $this->category();

        FixedExpense::create([
            'name' => 'Impuesto bimestral',
            'category_id' => $category->id,
            'average_amount' => 300000,
            'fortnight' => 1,
            'active' => true,
            'interval_months' => 2,
            'anchor_year' => 2026,
            'anchor_month' => 1,
        ]);

        // Enero es el ancla: febrero se salta, marzo vuelve a tocar.
        $febrero = $this->service->resolveBudget(2026, 2);
        $marzo = $this->service->resolveBudget(2026, 3);

        $this->assertSame(0, $febrero->expenses()->count());
        $this->assertSame(1, $marzo->expenses()->count());
    }

    public function test_el_resumen_suma_ingresos_gastos_y_balance(): void
    {
        $category = $this->category();
        $uno = $this->person('Persona uno');
        $dos = $this->person('Persona dos');

        $budget = $this->service->resolveBudget(2026, 3);

        Income::create([
            'monthly_budget_id' => $budget->id,
            'person_id' => $uno->id,
            'amount' => 2000000,
            'fortnight' => 1,
            'received_at' => '2026-03-10',
        ]);
        Income::create([
            'monthly_budget_id' => $budget->id,
            'person_id' => $dos->id,
            'amount' => 1500000,
            'fortnight' => 2,
            'received_at' => '2026-03-25',
        ]);
        Expense::create([
            'monthly_budget_id' => $budget->id,
            'category_id' => $category->id,
            'person_id' => $uno->id,
            'description' => 'Mercado',
            'amount' => 400000,
            'spent_at' => '2026-03-10',
            'fortnight' => 1,
        ]);

        $summary = $this->service->summary($budget->fresh());

        $this->assertEqualsWithDelta(3500000, $summary['total_income'], 0.01);
        $this->assertEqualsWithDelta(400000, $summary['total_expense'], 0.01);
    }

    public function test_el_ahorro_no_cuenta_como_gasto_del_mes(): void
    {
        $ahorro = $this->category(Category::SAVINGS);
        $hogar = $this->category('Hogar');
        $uno = $this->person();

        $budget = $this->service->resolveBudget(2026, 3);

        Income::create([
            'monthly_budget_id' => $budget->id,
            'person_id' => $uno->id,
            'amount' => 1000000,
            'fortnight' => 1,
            'received_at' => '2026-03-10',
        ]);
        Expense::create([
            'monthly_budget_id' => $budget->id,
            'category_id' => $hogar->id,
            'person_id' => $uno->id,
            'description' => 'Arriendo',
            'amount' => 600000,
            'spent_at' => '2026-03-05',
            'fortnight' => 1,
        ]);
        Expense::create([
            'monthly_budget_id' => $budget->id,
            'category_id' => $ahorro->id,
            'person_id' => $uno->id,
            'description' => 'A la alcancía',
            'amount' => 200000,
            'spent_at' => '2026-03-06',
            'fortnight' => 1,
        ]);

        $summary = $this->service->summary($budget->fresh());

        // Mover plata al ahorro no empobrece el mes: sigue siendo tuya.
        $this->assertEqualsWithDelta(600000, $summary['total_expense'], 0.01);
    }
}
