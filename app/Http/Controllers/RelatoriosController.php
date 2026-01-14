<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RelatoriosController extends Controller
{
    public function exportar(Request $request): StreamedResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'formato' => ['required', 'in:csv'],
            'periodo_inicio' => ['required', 'date'],
            'periodo_fim' => ['required', 'date', 'after_or_equal:periodo_inicio'],
            'categorias' => ['nullable', 'array'],
            'categorias.*' => ['integer'],
            'contas' => ['nullable', 'array'],
            'contas.*' => ['integer'],
            'tipos' => ['nullable', 'array'],
            'tipos.*' => ['in:income,expense'],
            'status' => ['nullable', 'array'],
            'status.*' => ['in:pending,paid,received'],
        ]);

        $inicio = CarbonImmutable::parse($data['periodo_inicio'])->toDateString();
        $fim = CarbonImmutable::parse($data['periodo_fim'])->toDateString();

        $query = Transaction::query()
            ->with(['category', 'account'])
            ->where('user_id', $user->id)
            ->whereBetween('transaction_date', [$inicio, $fim])
            ->orderBy('transaction_date')
            ->orderBy('id');

        if (!empty($data['categorias'])) {
            $query->whereIn('category_id', $data['categorias']);
        }

        if (!empty($data['contas'])) {
            $query->whereIn('account_id', $data['contas']);
        }

        if (!empty($data['tipos'])) {
            $query->whereIn('kind', $data['tipos']);
        }

        if (!empty($data['status'])) {
            $query->whereIn('status', $data['status']);
        }

        $filename = 'kitamo_relatorio_' . $inicio . '_a_' . $fim . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Data',
                'Descrição',
                'Categoria',
                'Conta',
                'Tipo',
                'Valor',
                'Moeda',
                'Status',
            ]);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $t) {
                    fputcsv($out, [
                        optional($t->transaction_date)->format('Y-m-d') ?? '',
                        $t->description,
                        $t->category?->name ?? '',
                        $t->account?->name ?? '',
                        $t->kind,
                        number_format((float) $t->amount, 2, '.', ''),
                        $t->moeda ?? 'BRL',
                        $t->status,
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

