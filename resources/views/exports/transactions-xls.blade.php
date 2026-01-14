<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8" />
        <title>Exportação de transações - Kitamo</title>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th,
            td {
                border: 1px solid #d1d5db;
                padding: 8px;
                font-size: 12px;
                vertical-align: top;
            }
            th {
                background: #f3f4f6;
                font-weight: 700;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Conta</th>
                    <th>Valor</th>
                    <th>Parcelas</th>
                    <th>Tags</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $t)
                    <tr>
                        <td>{{ $t->transaction_date?->format('d/m/Y') ?? '' }}</td>
                        <td>{{ $t->kind }}</td>
                        <td>{{ $t->status }}</td>
                        <td>{{ $t->description }}</td>
                        <td>{{ $t->category?->name ?? '' }}</td>
                        <td>{{ $t->account?->name ?? '' }}</td>
                        <td>{{ number_format((float) $t->amount, 2, ',', '.') }}</td>
                        <td>{{ $t->installment_label ?? '' }}</td>
                        <td>{{ is_array($t->tags) ? implode(',', $t->tags) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>

