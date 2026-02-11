<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardRoutesTest extends TestCase
{
    use RefreshDatabase;
    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuário e token para autenticação
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_deve_retornar_dados_do_dashboard_com_sucesso(): void
    {
        // ARRANGE: Simulando as chamadas de API externas
        Http::fake([
            'mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/*' => Http::response([
                'data' => [
                    ['idSistema' => 64, 'volume' => 50.5]
                ]
            ], 200),
            
            'mananciais.sabesp.com.br/api/v4/sistemas' => Http::response([
                'data' => [
                    ['id' => 64, 'name' => 'Cantareira']
                ]
            ], 200),
        ]);

        // ACT: Fazendo a requisição para sua rota
        // Certifique-se de que a rota existe em routes/api.php ou web.php
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)->getJson('/api/dashboard/data');

        // ASSERT: Verificações
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'idSistema' => 64,
                     'name_sistema' => 'Cantareira'
                 ]);
    }
}