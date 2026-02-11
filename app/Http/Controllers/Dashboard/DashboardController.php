<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {}

    public function getData() {
        $date = '2026-02-11';
        // 
        $dataSabesp = $this->service->buscarDadosSabesp($date);
        $dataSabespParametros = $this->service->buscarDadosSabespParametros();

        $data = $this->service->mergeData($dataSabesp, $dataSabespParametros);

        return response()->json(
            $data
        );

    }


}