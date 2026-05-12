# Roadmap: Multi-tenancy por Núcleo Familiar (Households)

> Documento de planificación para convertir la app **Cuentas** de single-tenant
> a multi-tenant, con la mira puesta en comercialización como SaaS.
>
> **Estado**: planificado, no implementado.
> **Audiencia**: el dev que retome esto en el futuro (probablemente el mismo
> Andrés en unos meses).

---

## 1. Contexto y objetivo

### Estado actual (single-tenant)

La app maneja **un solo núcleo familiar**. Todas las tablas (`people`,
`categories`, `fixed_expenses`, `monthly_budgets`, `incomes`, `expenses`) son
globales. Cualquier usuario autenticado ve los datos de todos.

Los roles `manager` / `viewer` existen pero operan sobre el mismo conjunto de
datos.

### Objetivo

Permitir que **múltiples núcleos familiares** convivan en la misma instancia, cada
uno aislado:

- Núcleo "Andrés & Laura" tiene a Andrés y Laura como personas y sus propios
  presupuestos, gastos, categorías.
- Núcleo "Pepito & Pepita" tiene sus propias personas y datos, **invisible**
  para el primer núcleo.
- Cada núcleo puede tener N usuarios (managers + viewers) con acceso solo a
  sus datos.

Esto habilita el modelo de negocio SaaS: cada familia paga su suscripción y
opera en su sandbox.

---

## 2. Decisión arquitectónica

### Patrón elegido: **shared database, tenant column**

| Patrón | Aislamiento | Costo infra | Complejidad | Recomendado |
|---|---|---|---|---|
| **Columna `tenant_id` en cada tabla** | Lógico | Bajo | Baja | ✅ |
| Schema-per-tenant (Postgres) | Fuerte | Medio | Media | Overkill |
| Database-per-tenant | Máximo | Alto | Alta | Solo enterprise |

**Por qué columna `tenant_id`:**
- Una sola base de datos, fácil de mantener
- Backups, migraciones y deploys triviales
- Cross-tenant analytics si algún día se necesitan (con cuidado de privacidad)
- Suficiente aislamiento para B2C de presupuesto familiar
- Laravel + Eloquent lo soporta nativo con `addGlobalScope`

### Paquetes a evaluar

- **`stancl/tenancy`** — robusto, soporta multi-DB. Overkill para esta app
- **`spatie/laravel-multitenancy`** — más simple, soporta single-DB con tenant column
- **DIY** — para una app de este tamaño, hacerlo manual es viable y nos deja control total

**Recomendación**: empezar **DIY** (siguiendo el manual de Laravel sobre Global
Scopes). Si crece, migrar a `spatie/laravel-multitenancy`.

---

## 3. Modelo de datos

### Nueva tabla: `households`

```php
Schema::create('households', function (Blueprint $table) {
    $table->id();
    $table->string('name');                     // "Familia Morales", "Hogar Pepe"
    $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('plan', 30)->default('free'); // free | pro | family
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamps();
});
```

### Agregar `household_id` a las tablas existentes

Migración:

```php
foreach (['users', 'people', 'categories', 'fixed_expenses',
          'monthly_budgets', 'incomes', 'expenses'] as $table) {
    Schema::table($table, function (Blueprint $t) {
        $t->foreignId('household_id')->nullable()->after('id')
          ->constrained()->cascadeOnDelete();
        $t->index('household_id');
    });
}
```

**Índices compuestos** para queries comunes:

```php
Schema::table('monthly_budgets', function (Blueprint $t) {
    $t->dropUnique(['year', 'month']);
    $t->unique(['household_id', 'year', 'month']);
});

Schema::table('expenses', function (Blueprint $t) {
    $t->index(['household_id', 'monthly_budget_id']);
    $t->index(['household_id', 'spent_at']);
});

Schema::table('incomes', function (Blueprint $t) {
    $t->index(['household_id', 'monthly_budget_id']);
});
```

### Backfill (migración de datos existentes)

```php
public function up(): void
{
    // Crear el household por defecto y asignar todos los datos actuales
    $defaultHouseholdId = DB::table('households')->insertGetId([
        'name' => 'Hogar principal',
        'plan' => 'pro',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    foreach (['users', 'people', 'categories', 'fixed_expenses',
              'monthly_budgets', 'incomes', 'expenses'] as $table) {
        DB::table($table)->update(['household_id' => $defaultHouseholdId]);
    }

    // Marcar el primer manager como owner
    $owner = DB::table('users')->where('role', 'manager')->first();
    if ($owner) {
        DB::table('households')->where('id', $defaultHouseholdId)
            ->update(['owner_user_id' => $owner->id]);
    }

    // Hacer la columna NOT NULL después del backfill
    foreach (['users', 'people', 'categories', 'fixed_expenses',
              'monthly_budgets', 'incomes', 'expenses'] as $table) {
        Schema::table($table, function (Blueprint $t) {
            $t->foreignId('household_id')->nullable(false)->change();
        });
    }
}
```

---

## 4. Aislamiento automático: Global Scopes

### Trait reutilizable

```php
// app/Models/Concerns/BelongsToHousehold.php
namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToHousehold
{
    protected static function bootBelongsToHousehold(): void
    {
        // Filtro automático en todas las queries
        static::addGlobalScope('household', function (Builder $q) {
            $hid = auth()->user()?->household_id;
            if ($hid) $q->where($q->getModel()->getTable() . '.household_id', $hid);
        });

        // Auto-asignar al crear
        static::creating(function ($model) {
            if (! $model->household_id && auth()->check()) {
                $model->household_id = auth()->user()->household_id;
            }
        });
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Household::class);
    }
}
```

### Aplicar a todos los modelos

```php
// app/Models/Person.php
use App\Models\Concerns\BelongsToHousehold;

class Person extends Model
{
    use BelongsToHousehold;
    // ...
}
```

Repetir para `Category`, `FixedExpense`, `MonthlyBudget`, `Income`, `Expense`.

### Excepción para super-admin

Si quieres un panel admin que vea todo:

```php
// Bypass scope
Person::withoutGlobalScope('household')->get();
```

---

## 5. Modelo `User` y `Household`

### Household

```php
class Household extends Model
{
    protected $fillable = ['name', 'owner_user_id', 'plan', 'trial_ends_at'];
    protected $casts = ['trial_ends_at' => 'datetime'];

    public function users(): HasMany       { return $this->hasMany(User::class); }
    public function people(): HasMany      { return $this->hasMany(Person::class); }
    public function budgets(): HasMany     { return $this->hasMany(MonthlyBudget::class); }
    public function owner(): BelongsTo     { return $this->belongsTo(User::class, 'owner_user_id'); }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
```

### User

```php
class User extends Authenticatable
{
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function isOwner(): bool
    {
        return $this->household?->owner_user_id === $this->id;
    }
}
```

---

## 6. Categorías protegidas por household

Las 4 categorías protegidas (Ahorro, Alimentación, Hogar, Servicios) dejan de ser
globales — cada household debe tener su propia copia auto-creada al registrarse.

```php
// app/Services/HouseholdBootstrapper.php
class HouseholdBootstrapper
{
    public function bootstrap(Household $household): void
    {
        $defaults = [
            ['name' => 'Hogar',        'icon' => 'bi-house-door',  'color' => '#0d6efd'],
            ['name' => 'Alimentación', 'icon' => 'bi-basket',      'color' => '#198754'],
            ['name' => 'Salidas',      'icon' => 'bi-cup-straw',   'color' => '#fd7e14'],
            ['name' => 'Moto',         'icon' => 'bi-bicycle',     'color' => '#6f42c1'],
            ['name' => 'Servicios',    'icon' => 'bi-lightning',   'color' => '#ffc107'],
            ['name' => 'Salud',        'icon' => 'bi-heart-pulse', 'color' => '#dc3545'],
            ['name' => 'Deudas',       'icon' => 'bi-credit-card', 'color' => '#6c757d'],
            ['name' => 'Ahorro',       'icon' => 'bi-piggy-bank',  'color' => '#20c997'],
            ['name' => 'Varios',       'icon' => 'bi-three-dots',  'color' => '#adb5bd'],
        ];

        foreach ($defaults as $c) {
            $household->categories()->create($c);
        }
    }
}
```

Llamado en: registro de nuevo household.

---

## 7. Flujo de registro (onboarding)

### Registro público

`POST /register` crea Household + User owner + categorías base:

```php
public function register(RegisterRequest $request, HouseholdBootstrapper $bootstrap)
{
    DB::transaction(function () use ($request, $bootstrap, &$user) {
        $household = Household::create([
            'name'           => $request->household_name,
            'plan'           => 'free',
            'trial_ends_at'  => now()->addDays(14),
        ]);

        $user = User::create([
            'household_id' => $household->id,
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => 'manager',
        ]);

        $household->update(['owner_user_id' => $user->id]);
        $bootstrap->bootstrap($household);
    });

    Auth::login($user);
    return redirect()->route('onboarding.show'); // tour inicial
}
```

### Onboarding (primer login)

Wizard de 3 pasos:
1. "¿Cuántas personas aportan ingresos en tu hogar?" → crea `people`
2. "Agrega tus gastos fijos recurrentes" → crea `fixed_expenses`
3. "Registra el ingreso de este mes" → crea primer `Income`

### Invitación de usuarios al núcleo

Manager invita por email:

```php
Route::post('/users/invite', [UserController::class, 'invite']);

class UserController
{
    public function invite(Request $request)
    {
        $invite = Invitation::create([
            'household_id' => auth()->user()->household_id,
            'email'        => $request->email,
            'role'         => $request->role,
            'token'        => Str::random(64),
            'expires_at'   => now()->addDays(7),
        ]);

        Mail::to($request->email)->send(new HouseholdInvitation($invite));
    }
}
```

El destinatario abre el link `/accept-invite/{token}` → crea su user dentro
del household existente.

---

## 8. Seguridad: validación cross-tenant

### Riesgo

Aunque los Global Scopes filtran SELECTs, un usuario malintencionado puede
intentar pasar IDs por URL:

`PUT /expenses/12345` donde `12345` es de otro household → 403 o silently fail.

### Capa adicional: Form Request validation

```php
class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');
        return $expense->household_id === auth()->user()->household_id;
    }
}
```

Y/o un middleware que valida que cada Route Model Binding pertenece al
household del usuario.

### Tests obligatorios

```php
public function test_user_cannot_access_other_household_expense()
{
    $household1 = Household::factory()->create();
    $household2 = Household::factory()->create();
    $user1 = User::factory()->for($household1)->create();
    $expense2 = Expense::factory()->for($household2)->create();

    $this->actingAs($user1)
         ->delete("/expenses/{$expense2->id}")
         ->assertStatus(403);

    $this->assertDatabaseHas('expenses', ['id' => $expense2->id]);
}
```

Hacer esto para cada modelo: `people`, `categories`, `fixed_expenses`,
`monthly_budgets`, `incomes`, `expenses`, `users`.

---

## 9. Cambios en `BudgetService`

El servicio actual está **bien**, pero hay que cuidar las queries que NO
pasan por Eloquent (raw queries). Auditar:

```php
// app/Services/BudgetService.php — verificar cada query

// ✅ OK: Person::all() respeta global scope
Person::orderBy('name')->get()

// ✅ OK: $budget->incomes respeta scope a través de relación
$budget->incomes->where('person_id', $person->id)

// ⚠️ Atención: queries con whereIn explícitas
Expense::whereIn('monthly_budget_id', $previousIds)  // ya filtrado, OK
```

Como regla: si todas las relaciones del modelo tienen el scope, las queries
encadenadas heredan el aislamiento.

---

## 10. UI: contextualización del núcleo

### Navbar

Mostrar el nombre del household activo:

```blade
<span class="navbar-brand">
    <i class="bi bi-gem"></i> Cuentas · {{ auth()->user()->household->name }}
</span>
```

### Settings del household

Nueva ruta `/household` (solo owner):
- Cambiar nombre del hogar
- Ver plan actual
- Ver fecha de fin de trial
- Botón "Cancelar suscripción" (Stripe portal)
- Lista de usuarios invitados + estado

### Multi-household (futuro lejano)

Si un user pertenece a >1 household (raro, pero posible: alguien que ayude a
varias familias), añadir un selector en el navbar para cambiar de contexto.
Por ahora: 1 user = 1 household.

---

## 11. Suscripciones (SaaS)

### Stack recomendado

- **Laravel Cashier** + **Stripe**
- Planes: `free` (1 user, 1 household, datos limitados a 3 meses) /
  `pro` ($X/mes, todo ilimitado, 30 días trial)

### Modelo

```php
Schema::create('subscriptions', function (Blueprint $t) {
    $t->id();
    $t->foreignId('household_id')->constrained()->cascadeOnDelete();
    $t->string('stripe_id')->unique();
    $t->string('stripe_status');
    $t->string('stripe_price');
    $t->integer('quantity')->nullable();
    $t->timestamp('trial_ends_at')->nullable();
    $t->timestamp('ends_at')->nullable();
    $t->timestamps();
});
```

### Middleware de plan

```php
class EnsureActivePlan
{
    public function handle(Request $request, Closure $next)
    {
        $household = auth()->user()->household;
        if ($household->plan === 'free' && $household->budgets()->count() > 3) {
            return redirect()->route('billing.upgrade')
                ->with('error', 'El plan gratuito limita a 3 meses. Suscríbete para continuar.');
        }
        return $next($request);
    }
}
```

---

## 12. Esfuerzo estimado

### Fase 1: Multi-tenancy básico

| Tarea | Tiempo |
|---|---|
| Migraciones `households` + columnas `household_id` + backfill | 4-6h |
| Trait `BelongsToHousehold` + aplicar a 6 modelos | 2h |
| Modelo `Household` + `HouseholdBootstrapper` | 2h |
| Registro público + login modificado | 4h |
| Invitar usuarios al núcleo (con email) | 6-8h |
| Tests de aislamiento cross-tenant (mínimo 20 tests) | 4-6h |
| Refactor de seeders para crear household demo | 2h |
| Ajustes UI (navbar, settings) | 4h |
| **Total Fase 1** | **~3-4 días** |

### Fase 2: SaaS comercializable

| Tarea | Tiempo |
|---|---|
| Integración Stripe (Laravel Cashier) | 1-2 días |
| Página de pricing + checkout | 1 día |
| Webhooks Stripe (subscription.updated, invoice.paid, etc.) | 1 día |
| Onboarding wizard (3 pasos) | 1 día |
| Sistema de emails transaccional (Resend/Mailgun) | 1 día |
| Multi-idioma (al menos ES + EN) | 1 día |
| Multi-moneda en `incomes`/`expenses` (COP, USD, MXN…) | 1-2 días |
| Política de privacidad, ToS, GDPR/Habeas Data | 1 día |
| Backups automáticos (spatie/laravel-backup → S3) | medio día |
| Monitoreo (Sentry, telemetry) | medio día |
| Marketing site / landing | 2-3 días |
| **Total Fase 2** | **~2-3 semanas** |

### Total para SaaS funcional: **3-4 semanas**

---

## 13. Riesgos y consideraciones

### Performance

- **SQLite no escala** para multi-tenant con muchos usuarios. **Migrar a
  PostgreSQL** antes del lanzamiento público.
- Connection pooling (PgBouncer) si la concurrencia crece.
- Cache de queries pesadas: `BudgetService::cumulativeLeftover` puede ser
  costoso si hay muchos meses; considerar cachear por household.

### Privacidad y compliance

- **Colombia**: cumplir Habeas Data (Ley 1581/2012). Política de privacidad
  obligatoria, opción de eliminar cuenta + todos los datos.
- **Europa (si vendes allá)**: GDPR. Exportar datos del usuario, derecho al
  olvido.
- Cifrar datos sensibles en reposo (ya que es info financiera).

### Backups

- Backup diario de Postgres a S3 con retención de 30 días.
- Probar restore al menos 1 vez al mes.

### Modelo "free" como gancho

- Generoso pero limitado: 1 household, 2 users, 3 meses de historial.
- Trial Pro de 14 días al registrarse.
- Conversion target: 5-10% de free → paid.

### Soporte

- Helpdesk básico (Crisp, Intercom o Helpscout).
- FAQ + onboarding videos (Loom).

---

## 14. Checklist de migración (cuando ejecutes Fase 1)

- [ ] Crear branch `feature/multi-tenancy`
- [ ] Migración: crear tabla `households`
- [ ] Migración: agregar `household_id` a las 7 tablas
- [ ] Migración: backfill + cambiar a NOT NULL
- [ ] Migración: índices compuestos
- [ ] Crear `app/Models/Household.php`
- [ ] Crear trait `app/Models/Concerns/BelongsToHousehold.php`
- [ ] Aplicar trait a `User`, `Person`, `Category`, `FixedExpense`,
      `MonthlyBudget`, `Income`, `Expense`
- [ ] Crear `app/Services/HouseholdBootstrapper.php`
- [ ] Reemplazar `DatabaseSeeder` para crear 1 household demo
- [ ] Refactor `Category::PROTECTED` (ya no global, por household)
- [ ] Crear `RegisterController` + vista
- [ ] Crear sistema de invitaciones (tabla, controller, mail, vista)
- [ ] Tests de aislamiento (1 por modelo mínimo)
- [ ] UI: navbar muestra nombre del household
- [ ] UI: nueva pantalla `/household/settings`
- [ ] Actualizar README + este documento (marcar como ✅ implementado)

---

## 15. Lo que ya está bien y no requiere cambios

- ✅ `BudgetService` (carry-over, sobrantes, summary) — agnóstico al tenant
- ✅ UI/UX (Bootstrap, vinotinto, SweetAlert) — solo agregar contexto household
- ✅ Roles `manager`/`viewer` — siguen aplicando dentro del household
- ✅ Bloqueo de meses pasados — funciona igual
- ✅ Categorías protegidas — la lógica se mantiene, solo cambia que son
      por household
- ✅ Edit/delete con confirmación SweetAlert — sin cambios
- ✅ Auth (login/logout) — solo agregar `household_id` al user logueado

---

## Apéndice: alternativa con `spatie/laravel-multitenancy`

Si la implementación DIY se vuelve compleja, evaluar este paquete:

```bash
composer require spatie/laravel-multitenancy
php artisan vendor:publish --provider="Spatie\Multitenancy\MultitenancyServiceProvider"
```

Pros:
- Manejo automático de scopes
- Soporte para tenant-specific config (queues, cache, mail)
- Documentación robusta
- Battle-tested

Cons:
- Asume un patrón específico (subdomain.app.com por defecto)
- Curva de aprendizaje
- Una abstracción más en la pila

**Recomendación**: empezar DIY, migrar a Spatie si crece >100 households.
