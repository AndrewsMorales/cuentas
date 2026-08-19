# Cuentas — Presupuesto del hogar

App Laravel + Bootstrap 5 para administrar el presupuesto mensual del hogar de
dos personas. Diseñada responsive para **web y móvil** (FAB en
móvil, botón en la barra superior en escritorio).

## Funcionalidad

- **Ingresos** por persona y por quincena (1ª = días 1-15, 2ª = días 16-31).
- **Categorías** de gasto (Hogar, Alimentación, Salidas, Moto, Servicios, Salud,
  N personalizadas) con icono y color.
- **Gastos fijos** plantilla (ej. *Recibo de luz, promedio $250.000*) que se
  cargan **automáticamente cada mes nuevo** y quedan listos para asignar persona.
- **Gasto rápido** desde un solo botón (FAB en móvil, botón en navbar en
  escritorio): monto, descripción, categoría, persona y fecha → se descuenta de
  la persona indicada.
- **Resumen del mes**: ingresos, gastos, balance global, balance por persona,
  totales por categoría y desglose por quincena.
- **Histórico mensual** navegable (vista *Meses*).

## Arquitectura (MVC + Servicios)

```
app/
├── Models/              # Eloquent: Person, Category, FixedExpense,
│                        # MonthlyBudget, Income, Expense
├── Http/Controllers/    # Capa Controller (delgada): valida + delega
│   ├── DashboardController.php
│   ├── BudgetController.php
│   ├── CategoryController.php
│   ├── FixedExpenseController.php
│   ├── IncomeController.php
│   └── ExpenseController.php
└── Services/            # Capa Service: lógica de negocio
    ├── BudgetService.php   # crea mes, materializa fijos, calcula resumen
    ├── ExpenseService.php  # alta, asignación, edición de gasto
    └── IncomeService.php   # alta/edición/baja de ingreso

resources/views/
├── layouts/app.blade.php          # Layout responsive con navbar + FAB
├── partials/quick_expense_modal.blade.php
├── dashboard.blade.php
├── budgets/{index,show}.blade.php
├── categories/{index,form}.blade.php
├── fixed_expenses/{index,form}.blade.php
├── incomes/index.blade.php
└── expenses/index.blade.php

routes/web.php           # Rutas RESTful
database/
├── migrations/          # Esquema completo
└── seeders/             # datos de demostración: 2 personas, categorías y gastos fijos
```

### Modelo de datos

| Tabla              | Descripción                                                         |
|--------------------|---------------------------------------------------------------------|
| `people`           | Personas que aportan ingresos                                       |
| `categories`       | Categorías de gasto (nombre, icono, color)                          |
| `fixed_expenses`   | Plantilla de gasto fijo (nombre, categoría, monto promedio, quincena)|
| `monthly_budgets`  | Mes (unique [year, month])                                          |
| `incomes`          | Ingreso de una persona, mes y quincena                              |
| `expenses`         | Gasto ejecutado (mes, categoría, persona, monto, fecha, quincena, flag `is_fixed_template`) |

### Flujo: nuevo mes

1. Al entrar al dashboard, `BudgetService::resolveBudget($year, $month)` crea el
   `MonthlyBudget` si no existe.
2. En la creación, `seedFixedExpenses()` copia cada `FixedExpense` activo como
   un `Expense` del mes con `person_id = null` (pendiente) y `is_fixed_template
   = true`.
3. El usuario asigna la persona desde *Gastos* con un clic (formulario en línea
   `PATCH /expenses/{id}/assign`).
4. Cualquier gasto adicional se agrega desde el FAB / botón "Agregar gasto" que
   abre el modal global.

## Stack

- **Laravel 11** + **PHP 8.3**
- **SQLite** (archivo `database/database.sqlite`)
- **Bootstrap 5.3** + **Bootstrap Icons** vía CDN (cero build step)
- Locale `es`, fechas con Carbon en español

## Instalación local

```bash
cd cuentas
composer install
php artisan migrate --seed
php artisan serve
```

Abrir <http://127.0.0.1:8000>. El primer acceso genera el mes actual con los 3
gastos fijos de ejemplo (Arriendo, Internet, Recibo de luz).

## Rutas principales

| Método | URI                                  | Nombre                   |
|--------|--------------------------------------|--------------------------|
| GET    | `/`                                  | `dashboard`              |
| GET    | `/budgets`                           | `budgets.index`          |
| GET    | `/budgets/{year}/{month}`            | `budgets.show`           |
| POST   | `/budgets/{year}/{month}/reload-fixed` | `budgets.reload-fixed` |
| GET    | `/incomes`                           | `incomes.index`          |
| POST   | `/incomes`                           | `incomes.store`          |
| GET    | `/expenses`                          | `expenses.index`         |
| POST   | `/expenses`                          | `expenses.store`         |
| PATCH  | `/expenses/{expense}/assign`         | `expenses.assign`        |
| `resource` | `/categories`                    | `categories.*`           |
| `resource` | `/fixed-expenses`                | `fixed-expenses.*`       |

## Convenciones quincenales

- **Quincena 1**: días 1–15 del mes.
- **Quincena 2**: días 16 al último del mes.
- Si en el modal rápido no se elige quincena, se deriva automáticamente de la
  fecha del gasto (`ExpenseService::fortnightFromDate`).
- Cada `FixedExpense` se fija en una quincena específica (config en el alta).

## Personalización

- **Agregar persona**: insertar en la tabla `people` (no hay UI todavía; pensado
  para 2 personas).
- **Cambiar gastos fijos**: menú *Gastos fijos* → editar montos, quincena o
  desactivar.
- **Crear categorías nuevas**: menú *Categorías* → con icono Bootstrap y color.
