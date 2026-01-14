<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function transactions(Request $request): Response
    {
        $user = $request->user();

        $data = $request->validate([
            'format' => ['required', 'in:csv,excel'],
        ]);

        $format = $data['format'];

        $transactions = Transaction::query()
            ->where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $today = now()->format('Y-m-d');

        if ($format === 'csv') {
            $filename = "kitamo-transacoes-{$today}.csv";

            return response()->streamDownload(function () use ($transactions) {
                $out = fopen('php://output', 'wb');
                if ($out === false) {
                    return;
                }

                fwrite($out, "\xEF\xBB\xBF");

                fputcsv($out, [
                    'Data',
                    'Tipo',
                    'Status',
                    'Descrição',
                    'Categoria',
                    'Conta',
                    'Valor',
                    'Parcelas',
                    'Tags',
                ], ';');

                foreach ($transactions as $t) {
                    $date = $t->transaction_date?->format('d/m/Y') ?? '';
                    $installment = $t->installment_label ?? '';
                    $tags = is_array($t->tags) ? implode(',', $t->tags) : '';

                    fputcsv($out, [
                        $date,
                        $t->kind,
                        $t->status,
                        $t->description,
                        $t->category?->name ?? '',
                        $t->account?->name ?? '',
                        number_format((float) $t->amount, 2, ',', '.'),
                        $installment,
                        $tags,
                    ], ';');
                }

                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $filename = "kitamo-transacoes-{$today}.xls";

        $html = view('exports.transactions-xls', [
            'transactions' => $transactions,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

