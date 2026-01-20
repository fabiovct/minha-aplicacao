<?php

namespace App\Services;

use App\Models\Produto;

class ProductService
{
    public function listar()
    {
        return Produto::all();
    }

    public function criar(array $dados): Produto
    {
        return Produto::create($dados);
    }

    public function atualizar(int $id, array $dados): ?Produto
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return null;
        }

        $produto->update($dados);

        return $produto;
    }

    public function deletar(int $id): bool
    {
        $produto = Produto::find($id);

        if (!$produto) {
            return false;
        }

        $produto->delete();

        return true;
    }
}
