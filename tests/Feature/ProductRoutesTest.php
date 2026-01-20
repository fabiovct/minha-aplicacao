<?php

namespace Tests\Feature;

use App\Models\Produto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductRoutesTest extends TestCase
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

    /**
     * Testa listagem de produtos (GET /api/list)
     */
    public function test_list_products_returns_successful_response(): void
    {
        // Criar alguns produtos
        Produto::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/product');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                    'qtd',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertCount(3, $response->json());
    }

    /**
     * Testa listagem quando não há produtos
     */
    public function test_list_products_returns_empty_array_when_no_products(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/product');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * Testa criação de produto (POST /api/list)
     */
    public function test_create_product_with_valid_data(): void
    {
        $productData = [
            'name' => 'Produto Teste',
            'description' => 'Descrição do produto teste',
            'qtd' => 10,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/product', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'qtd',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'message' => 'Produto criado com sucesso',
                'data' => [
                    'name' => 'Produto Teste',
                    'description' => 'Descrição do produto teste',
                    'qtd' => 10,
                ]
            ]);

        $this->assertDatabaseHas('produtos', [
            'name' => 'Produto Teste',
            'description' => 'Descrição do produto teste',
            'qtd' => 10,
        ]);
    }

    /**
     * Testa criação de produto sem description
     */
    public function test_create_product_without_description(): void
    {
        $productData = [
            'name' => 'Produto Sem Descrição',
            'qtd' => 5,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/product', $productData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('produtos', [
            'name' => 'Produto Sem Descrição',
            'qtd' => 5,
            'description' => null,
        ]);
    }

    /**
     * Testa validação ao criar produto sem name
     */
    public function test_create_product_requires_name(): void
    {
        $productData = [
            'description' => 'Sem nome',
            'qtd' => 5,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Testa validação ao criar produto sem qtd
     */
    public function test_create_product_requires_qtd(): void
    {
        $productData = [
            'name' => 'Produto Sem Qtd',
            'description' => 'Teste',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qtd']);
    }

    /**
     * Testa validação de qtd negativo
     */
    public function test_create_product_qtd_must_be_positive(): void
    {
        $productData = [
            'name' => 'Produto',
            'qtd' => -1,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/product', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qtd']);
    }

    /**
     * Testa atualização de produto (PUT /api/list/{id})
     */
    public function test_edit_product_with_valid_data(): void
    {
        $produto = Produto::factory()->create([
            'name' => 'Produto Original',
            'description' => 'Descrição original',
            'qtd' => 10,
        ]);

        $updateData = [
            'name' => 'Produto Atualizado',
            'description' => 'Nova descrição',
            'qtd' => 20,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/product/{$produto->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'qtd',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'message' => 'Produto atualizado com sucesso',
                'data' => [
                    'name' => 'Produto Atualizado',
                    'description' => 'Nova descrição',
                    'qtd' => 20,
                ]
            ]);

        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'name' => 'Produto Atualizado',
            'description' => 'Nova descrição',
            'qtd' => 20,
        ]);
    }

    /**
     * Testa atualização parcial de produto
     */
    public function test_edit_product_partial_update(): void
    {
        $produto = Produto::factory()->create([
            'name' => 'Produto Original',
            'description' => 'Descrição original',
            'qtd' => 10,
        ]);

        $updateData = [
            'name' => 'Apenas Nome Atualizado',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/product/{$produto->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'name' => 'Apenas Nome Atualizado',
            'description' => 'Descrição original', // Mantém o valor original
            'qtd' => 10, // Mantém o valor original
        ]);
    }

    /**
     * Testa atualização de produto inexistente
     */
    public function test_edit_nonexistent_product_returns_404(): void
    {
        $updateData = [
            'name' => 'Produto Inexistente',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/product/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Produto não encontrado'
            ]);
    }

    /**
     * Testa validação ao atualizar produto com qtd negativo
     */
    public function test_edit_product_qtd_must_be_positive(): void
    {
        $produto = Produto::factory()->create();

        $updateData = [
            'qtd' => -5,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/product/{$produto->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qtd']);
    }

    /**
     * Testa exclusão de produto (DELETE /api/list/{id})
     */
    public function test_delete_product_successfully(): void
    {
        $produto = Produto::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/product/{$produto->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Produto deletado com sucesso'
            ]);

        $this->assertDatabaseMissing('produtos', [
            'id' => $produto->id,
        ]);
    }

    /**
     * Testa exclusão de produto inexistente
     */
    public function test_delete_nonexistent_product_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/product/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Produto não encontrado'
            ]);
    }

    /**
     * Testa que rotas requerem autenticação
     */
    public function test_routes_require_authentication(): void
    {
        // Test GET sem autenticação
        $response = $this->getJson('/api/product');
        $response->assertStatus(401);

        // Test POST sem autenticação
        $response = $this->postJson('/api/product', [
            'name' => 'Test',
            'qtd' => 10,
        ]);
        $response->assertStatus(401);

        // Test PUT sem autenticação
        $response = $this->putJson('/api/product/1', [
            'name' => 'Test',
        ]);
        $response->assertStatus(401);

        // Test DELETE sem autenticação
        $response = $this->deleteJson('/api/product/1');
        $response->assertStatus(401);
    }
}
