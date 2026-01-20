<?php

use App\Models\Produto;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_um_produto()
    {
        $service = new ProductService();

        $produto = $service->criar([
            'name' => 'Produto Teste',
            'description' => 'Descrição',
            'qtd' => 10,
        ]);

        $this->assertInstanceOf(Produto::class, $produto);
    }
}
