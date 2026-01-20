<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class CategoriesDedupe extends Command
{
    protected $signature = 'categories:dedupe
        {--apply : Aplica as mudanças no banco (move referências e apaga duplicatas)}
        {--user-id= : Limita a um user_id específico}
        {--skip-defaults : Ignora categorias padrão (user_id NULL)}';

    protected $description = 'Detecta e mescla categorias duplicadas (ex: Alimentação vs Alimentacao) por usuário + tipo.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $userId = $this->option('user-id');
        $skipDefaults = (bool) $this->option('skip-defaults');

        $query = Category::query()->select(['id', 'user_id', 'name', 'type', 'created_at']);

        if ($userId !== null && $userId !== '') {
            $query->where('user_id', (int) $userId);
        } elseif ($skipDefaults) {
            $query->whereNotNull('user_id');
        }

        try {
            $categories = $query->orderBy('user_id')->orderBy('type')->orderBy('name')->get();
        } catch (Throwable $e) {
            $message = $e->getMessage();
            if (str_contains($message, 'could not find driver')) {
                $this->error('Não foi possível acessar o banco: driver PDO não está instalado (ex: pdo_sqlite/pdo_mysql).');
                $this->line('Rode este comando no ambiente que possui o driver do seu DB habilitado.');
                return self::FAILURE;
            }

            $this->error('Falha ao acessar o banco de dados: ' . $message);
            return self::FAILURE;
        }
        if ($categories->isEmpty()) {
            $this->info('Nenhuma categoria encontrada para verificar.');
            return self::SUCCESS;
        }

        $groups = $categories->groupBy(fn (Category $c) => $this->groupKey($c));
        $dupeGroups = $groups->filter(fn ($items) => $items->count() > 1)->values();

        if ($dupeGroups->isEmpty()) {
            $this->info('Nenhuma categoria duplicada encontrada.');
            return self::SUCCESS;
        }

        $candidateIds = $dupeGroups->flatten()->pluck('id')->values();
        $refs = $this->loadReferenceCounts($candidateIds->all());

        $plan = [];
        foreach ($dupeGroups as $items) {
            $items = $items->values();
            /** @var Category $first */
            $first = $items->first();

            $picked = $this->pickCanonical($items, $refs);
            $others = $items->filter(fn (Category $c) => $c->id !== $picked->id)->values();

            $plan[] = [
                'user_id' => $first->user_id,
                'type' => $first->type,
                'normalized' => $this->normalizeName($first->name),
                'keep' => ['id' => $picked->id, 'name' => $picked->name, 'refs' => $refs[(int) $picked->id] ?? 0],
                'remove' => $others->map(fn (Category $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'refs' => $refs[(int) $c->id] ?? 0,
                ])->all(),
            ];
        }

        $this->warn('Duplicatas encontradas:');
        foreach ($plan as $row) {
            $scope = $row['user_id'] === null ? 'default' : ("user:" . $row['user_id']);
            $this->line("- {$scope} | {$row['type']} | {$row['normalized']}");
            $this->line("  manter: [{$row['keep']['id']}] {$row['keep']['name']} ({$row['keep']['refs']} refs)");
            foreach ($row['remove'] as $rm) {
                $this->line("  remover: [{$rm['id']}] {$rm['name']} ({$rm['refs']} refs)");
            }
        }

        if (!$apply) {
            $this->info('Dry-run: rode com --apply para aplicar as mudanças.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan) {
            foreach ($plan as $row) {
                $keepId = (int) $row['keep']['id'];
                foreach ($row['remove'] as $rm) {
                    $removeId = (int) $rm['id'];
                    if ($removeId === $keepId) continue;

                    DB::table('transactions')->where('category_id', $removeId)->update(['category_id' => $keepId]);
                    DB::table('recorrencia_grupos')->where('category_id', $removeId)->update(['category_id' => $keepId]);
                    DB::table('parcelamento_grupos')->where('category_id', $removeId)->update(['category_id' => $keepId]);
                    DB::table('recurring_transactions')->where('category_id', $removeId)->update(['category_id' => $keepId]);

                    Category::query()->where('id', $removeId)->delete();
                }
            }
        });

        $this->info('Categorias duplicadas mescladas com sucesso.');
        return self::SUCCESS;
    }

    private function groupKey(Category $category): string
    {
        $scope = $category->user_id === null ? 'default' : ('user:' . $category->user_id);
        return $scope . '|' . $category->type . '|' . $this->normalizeName($category->name);
    }

    private function normalizeName(string $name): string
    {
        return (string) Str::of($name)->trim()->lower()->ascii()->replaceMatches('/\s+/', ' ');
    }

    /**
     * @param array<int, int|string> $categoryIds
     * @return array<int, int>
     */
    private function loadReferenceCounts(array $categoryIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $categoryIds)));
        if (!$ids) return [];

        $tables = [
            'transactions' => 'category_id',
            'recorrencia_grupos' => 'category_id',
            'parcelamento_grupos' => 'category_id',
            'recurring_transactions' => 'category_id',
        ];

        $counts = [];
        foreach ($tables as $table => $column) {
            $rows = DB::table($table)
                ->select($column, DB::raw('COUNT(*) as c'))
                ->whereIn($column, $ids)
                ->groupBy($column)
                ->get();

            foreach ($rows as $row) {
                $id = (int) $row->$column;
                $counts[$id] = ($counts[$id] ?? 0) + (int) $row->c;
            }
        }

        return $counts;
    }

    /**
     * @param \Illuminate\Support\Collection<int, Category> $items
     * @param array<int, int> $refs
     */
    private function pickCanonical($items, array $refs): Category
    {
        /** @var Category $best */
        $best = $items->first();
        $bestRefs = (int) ($refs[(int) $best->id] ?? 0);
        $bestNameScore = $this->nameScore($best->name);

        foreach ($items as $candidate) {
            $candidateRefs = (int) ($refs[(int) $candidate->id] ?? 0);
            if ($candidateRefs > $bestRefs) {
                $best = $candidate;
                $bestRefs = $candidateRefs;
                $bestNameScore = $this->nameScore($candidate->name);
                continue;
            }

            if ($candidateRefs !== $bestRefs) continue;

            $candidateNameScore = $this->nameScore($candidate->name);
            if ($candidateNameScore > $bestNameScore) {
                $best = $candidate;
                $bestNameScore = $candidateNameScore;
                continue;
            }

            if ($candidateNameScore !== $bestNameScore) continue;

            if ((int) $candidate->id < (int) $best->id) {
                $best = $candidate;
            }
        }

        return $best;
    }

    private function nameScore(string $name): int
    {
        $ascii = $this->normalizeName($name);
        $raw = (string) Str::of($name)->trim()->lower()->replaceMatches('/\s+/', ' ');
        return max(0, strlen($raw) - strlen($ascii));
    }
}
