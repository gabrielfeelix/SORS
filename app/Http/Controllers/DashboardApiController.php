<?php

namespace App\Http\Controllers;

use App\Services\ProjecaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function projecao(Request $request, ProjecaoService $service): JsonResponse
    {
        $user = $request->user();

        return response()->json(
            $service->calcularProjecao30Dias($user->id),
        );
    }

    public function insights(Request $request, ProjecaoService $service): JsonResponse
    {
        $user = $request->user();

        return response()->json(
            $service->gerarInsights($user->id),
        );
    }
}
