<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Support\Facades\Http;

class DashboardService
{

    public function buscarDadosSabesp($data)
    {
        // Monta a URL com a data dinâmica
        $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/{$data}";

        // Faz a requisição GET
        $response = Http::get($url);

        // Verifica se a requisição foi bem-sucedida
        if ($response->successful()) {
            // Retorna os dados como array
            $dados = $response->json();
            
            return $dados;
        }

        // Caso ocorra erro (404, 500, etc)
        return false;
    }

        public function buscarDadosSabespParametros()
        {
            // Monta a URL com a data dinâmica
            $url = "https://mananciais.sabesp.com.br/api/v4/sistemas";

            // Faz a requisição GET
            $response = Http::get($url);

            // Verifica se a requisição foi bem-sucedida
            if ($response->successful()) {
                // Retorna os dados como array
                $dados = $response->json();
                
                return $dados;
            }

            // Caso ocorra erro (404, 500, etc)
            return false;
        }

        public function mergeData($data, $parametros)
        {
            $parametro = collect($parametros['data'])->firstWhere('id', 64);
            foreach ($data['data'] as &$valueData) {
                $parametro = collect($parametros['data'])->firstWhere('id', $valueData['idSistema']);
                $valueData['name_sistema'] = $parametro['name'];
            }
            return $data;
        }

}