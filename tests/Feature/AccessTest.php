<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El presupuesto del hogar no es público: sin sesión no se ve nada.
 */
class AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visitante_es_enviado_al_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_el_login_es_accesible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_con_sesion_se_ve_el_tablero(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertStatus(200);
    }

    public function test_las_secciones_privadas_exigen_sesion(): void
    {
        foreach (['/expenses', '/incomes', '/categories', '/budgets'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }
}
