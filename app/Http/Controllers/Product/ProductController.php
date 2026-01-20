<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $service
    ) {}

    public function list(): JsonResponse
    {
        return response()->json(
            $this->service->listar()
        );
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'qtd' => 'required|integer|min:0',
        ]);

        $produto = $this->service->criar($validated);

        return response()->json([
            'message' => 'Produto criado com sucesso',
            'data' => $produto
        ], 201);
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'qtd' => 'sometimes|integer|min:0',
        ]);

        $produto = $this->service->atualizar($id, $validated);

        if (!$produto) {
            return response()->json([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Produto atualizado com sucesso',
            'data' => $produto
        ]);
    }

    public function delete($id): JsonResponse
    {
        if (!$this->service->deletar($id)) {
            return response()->json([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Produto deletado com sucesso'
        ]);
    }
}
