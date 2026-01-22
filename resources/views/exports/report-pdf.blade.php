<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8" />
        <title>Relatório - Kitamo</title>
        <style>
            * {
                font-family: DejaVu Sans, sans-serif;
            }
            body {
                font-size: 12px;
                color: #0f172a;
            }
            .muted {
                color: #64748b;
            }
            .header {
                margin-bottom: 14px;
            }
            .title {
                font-size: 18px;
                font-weight: 700;
                margin: 0;
            }
            .subtitle {
                margin: 4px 0 0 0;
                font-size: 11px;
            }
            .cards {
                display: table;
                width: 100%;
                margin: 12px 0 16px 0;
                border-collapse: separate;
                border-spacing: 8px;
            }
            .card {
                display: table-cell;
                width: 33.33%;
                padding: 12px;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                background: #ffffff;
                vertical-align: top;
            }
            .card-label {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: #64748b;
            }
            .card-value {
                margin-top: 8px;
                font-size: 16px;
                font-weight: 800;
            }
            .card-value.green {
                color: #16a34a;
            }
            .card-value.red {
                color: #ef4444;
            }
            .section {
                margin-top: 14px;
            }
            .section-title {
                font-size: 12px;
                font-weight: 800;
                margin: 0 0 8px 0;
            }
            .bar-row {
                margin: 8px 0;
            }
            .bar-head {
                display: table;
                width: 100%;
                margin-bottom: 4px;
            }
            .bar-head .left,
            .bar-head .right {
                display: table-cell;
                font-size: 11px;
                font-weight: 700;
            }
            .bar-head .right {
                text-align: right;
                color: #334155;
            }
            .bar-track {
                width: 100%;
                height: 8px;
                background: #e2e8f0;
                border-radius: 999px;
                overflow: hidden;
            }
            .bar-fill {
                height: 8px;
                background: #14b8a6;
                border-radius: 999px;
            }
            table {
                border-collapse: collapse;
                width: 100%;
                margin-top: 8px;
            }
            th,
            td {
                border: 1px solid #e2e8f0;
                padding: 6px 8px;
                font-size: 10px;
                vertical-align: top;
            }
            th {
                background: #f1f5f9;
                font-weight: 800;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <p class="title">Relatório</p>
            <p class="subtitle muted">Período: {{ $inicio }} a {{ $fim }}</p>
        </div>

        @if ($includeSummary)
            @php
                $fmt = fn($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
            @endphp
            <div class="cards">
                <div class="card">
                    <div class="card-label">Receitas</div>
                    <div class="card-value green">{{ $fmt($incomeTotal) }}</div>
                </div>
                <div class="card">
                    <div class="card-label">Despesas</div>
                    <div class="card-value red">{{ $fmt($expenseTotal) }}</div>
                </div>
                <div class="card">
                    <div class="card-label">Balanço</div>
                    <div class="card-value {{ $netTotal >= 0 ? 'green' : 'red' }}">{{ $netTotal >= 0 ? '+' : '-' }} {{ $fmt(abs($netTotal)) }}</div>
                </div>
            </div>
        @endif

        @if ($includeCharts)
            @php
                $maxCat = max(1, (float) ($expenseByCategory->max() ?? 0));
                $maxDay = max(1, (float) ($expenseByDay->max() ?? 0));
            @endphp
            <div class="section">
                <p class="section-title">Despesas por categoria (Top 10)</p>
                @forelse ($expenseByCategory as $category => $total)
                    @php $pct = min(100, max(1, round(((float) $total / $maxCat) * 100))); @endphp
                    <div class="bar-row">
                        <div class="bar-head">
                            <div class="left">{{ $category }}</div>
                            <div class="right">{{ 'R$ ' . number_format((float) $total, 2, ',', '.') }}</div>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="muted">Sem despesas no período.</div>
                @endforelse
            </div>

            <div class="section">
                <p class="section-title">Despesas por dia</p>
                @forelse ($expenseByDay as $day => $total)
                    @continue($day === '')
                    @php $pct = min(100, max(1, round(((float) $total / $maxDay) * 100))); @endphp
                    <div class="bar-row">
                        <div class="bar-head">
                            <div class="left">{{ $day }}</div>
                            <div class="right">{{ 'R$ ' . number_format((float) $total, 2, ',', '.') }}</div>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="muted">Sem despesas no período.</div>
                @endforelse
            </div>
        @endif

        @if ($includeTransactions)
            <div class="section">
                <p class="section-title">Lista de transações</p>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Descrição</th>
                            <th>Categoria</th>
                            <th>Conta</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Valor</th>
                            <th>Tags</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $t)
                            <tr>
                                <td>{{ optional($t->transaction_date)->format('d/m/Y') ?? '' }}</td>
                                <td>{{ $t->description }}</td>
                                <td>{{ $t->category?->name ?? '' }}</td>
                                <td>{{ $t->account?->name ?? '' }}</td>
                                <td>{{ $t->kind }}</td>
                                <td>{{ $t->status }}</td>
                                <td>{{ 'R$ ' . number_format((float) $t->amount, 2, ',', '.') }}</td>
                                <td>{{ is_array($t->tags) ? implode(',', $t->tags) : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </body>
</html>

