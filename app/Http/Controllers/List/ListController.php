<?php

namespace App\Http\Controllers\List;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;

class ListController extends Controller
{

    public function list(): JsonResponse
    {
        $produtos = Produto::all();
        return response()->json($produtos);
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'qtd' => 'required|integer|min:0',
        ]);

        $produto = Produto::create($validated);

        return response()->json([
            'message' => 'Produto criado com sucesso',
            'data' => $produto
        ], 201);
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return response()->json([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'qtd' => 'sometimes|integer|min:0',
        ]);

        $produto->update($validated);

        return response()->json([
            'message' => 'Produto atualizado com sucesso',
            'data' => $produto
        ]);
    }

    public function delete($id): JsonResponse
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return response()->json([
                'message' => 'Produto não encontrado'
            ], 404);
        }

        $produto->delete();

        return response()->json([
            'message' => 'Produto deletado com sucesso'
        ]);
    }

}