<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RolesController extends Controller
{
    /**
     * @return array<int, array{key: string, label: string, description: string}>
     */
    private function roles(): array
    {
        return [
            ['key' => 'admin', 'label' => 'Admin', 'description' => 'Acesso total (painel de administração + gestão).'],
            ['key' => 'user', 'label' => 'Usuário', 'description' => 'Acesso padrão ao app (sem painel admin).'],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, description: string}>
     */
    private function permissions(): array
    {
        return [
            ['key' => 'transactions.read', 'label' => 'Ver transações', 'description' => 'Listar e visualizar movimentações.'],
            ['key' => 'transactions.create', 'label' => 'Criar transações', 'description' => 'Adicionar receitas/despesas/transferências.'],
            ['key' => 'transactions.update', 'label' => 'Editar transações', 'description' => 'Alterar categoria, conta, valor, etc.'],
            ['key' => 'transactions.delete', 'label' => 'Excluir transações', 'description' => 'Remover movimentações.'],
            ['key' => 'accounts.manage', 'label' => 'Gerenciar contas', 'description' => 'Criar/editar/excluir contas e carteiras.'],
            ['key' => 'categories.manage', 'label' => 'Gerenciar categorias', 'description' => 'Criar/editar/excluir categorias e ícones.'],
            ['key' => 'credit_cards.manage', 'label' => 'Gerenciar cartões', 'description' => 'Criar/editar/excluir cartões de crédito.'],
            ['key' => 'reports.export', 'label' => 'Exportar relatórios', 'description' => 'Gerar PDF/CSV/XLSX (quando habilitado).'],
            ['key' => 'goals.manage', 'label' => 'Gerenciar metas', 'description' => 'Criar/editar/excluir metas e depósitos.'],
            ['key' => 'settings.manage', 'label' => 'Ajustes gerais', 'description' => 'Preferências e telas de configurações.'],
            ['key' => 'admin.access', 'label' => 'Acessar painel admin', 'description' => 'Entrar na área de administração.'],
            ['key' => 'admin.users.manage', 'label' => 'Gerenciar usuários', 'description' => 'Editar/desativar/excluir usuários.'],
            ['key' => 'admin.roles.manage', 'label' => 'Gerenciar papéis', 'description' => 'Gerenciar papéis e permissões.'],
            ['key' => 'admin.logs.read', 'label' => 'Ver logs', 'description' => 'Visualizar logs de ações.'],
            ['key' => 'admin.emails.manage', 'label' => 'Gerenciar e-mails', 'description' => 'Comunicados e newsletters.'],
            ['key' => 'admin.news.manage', 'label' => 'Gerenciar novidades', 'description' => 'Criar e publicar novidades do sistema.'],
            ['key' => 'admin.leads.manage', 'label' => 'Gerenciar leads', 'description' => 'Listar/editar/excluir inscritos na newsletter.'],
        ];
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function defaultMatrix(): array
    {
        $permissionKeys = array_map(fn (array $p) => $p['key'], $this->permissions());

        $admin = [];
        foreach ($permissionKeys as $key) {
            $admin[$key] = true;
        }

        $userAllowed = [
            'transactions.read',
            'transactions.create',
            'transactions.update',
            'transactions.delete',
            'accounts.manage',
            'categories.manage',
            'credit_cards.manage',
            'reports.export',
            'goals.manage',
            'settings.manage',
        ];

        $user = [];
        foreach ($permissionKeys as $key) {
            $user[$key] = in_array($key, $userAllowed, true);
        }

        return [
            'admin' => $admin,
            'user' => $user,
        ];
    }

    public function index(): Response
    {
        $matrix = $this->defaultMatrix();

        $overrides = RolePermission::query()
            ->select(['role', 'permission', 'allowed'])
            ->whereIn('role', ['admin', 'user'])
            ->get();

        foreach ($overrides as $row) {
            $role = (string) $row->role;
            $perm = (string) $row->permission;
            if (! isset($matrix[$role]) || ! array_key_exists($perm, $matrix[$role])) {
                continue;
            }
            $matrix[$role][$perm] = (bool) $row->allowed;
        }

        return Inertia::render('Admin/Roles', [
            'roles' => $this->roles(),
            'permissions' => $this->permissions(),
            'matrix' => $matrix,
        ]);
    }

    public function update(Request $request, string $role): RedirectResponse
    {
        if (! in_array($role, ['admin', 'user'], true)) {
            abort(404);
        }

        $permissionKeys = array_map(fn (array $p) => $p['key'], $this->permissions());

        $data = $request->validate([
            'permissions' => ['required', 'array'],
        ]);

        /** @var array<string, mixed> $incoming */
        $incoming = $data['permissions'] ?? [];

        $normalized = [];
        foreach ($permissionKeys as $key) {
            $normalized[$key] = filter_var($incoming[$key] ?? false, FILTER_VALIDATE_BOOL);
        }

        DB::transaction(function () use ($role, $normalized) {
            RolePermission::query()->where('role', $role)->delete();

            $now = now();
            $rows = [];
            foreach ($normalized as $perm => $allowed) {
                $rows[] = [
                    'role' => $role,
                    'permission' => $perm,
                    'allowed' => (bool) $allowed,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            RolePermission::query()->insert($rows);
        });

        return back()->with('success', 'Permissões atualizadas.');
    }
}

