## Auditoria de lógica financeira (lotes)

Este documento registra o que foi auditado e o que foi ajustado em lotes de 2 itens (conforme combinado).

### Lote 1

**Tarefa 1 — Estrutura do banco (via migrations)**
- Observação: neste ambiente não é possível rodar `php artisan migrate:status` por falta de driver PDO (`could not find driver`). Auditoria feita via `database/migrations/*`.
- Tabelas relevantes confirmadas por migrations:
  - `accounts`, `transactions`, `categories`
  - `recorrencia_grupos`, `parcelamento_grupos`, `recurring_transactions`
  - `transferencias`
  - `tags`, `transaction_tags`
  - `kitamo_notifications`, `notification_preferences`
- Não existe (ainda) tabela de snapshots de saldo (RN07) — a navegação temporal precisa ser auditada em lote próprio.

**Tarefa 2/3 — Saldo e patrimônio (RN01/RN02)**
- Ajuste aplicado para manter consistência do “saldo/patrimônio” nos pontos onde era calculado somando todos os `current_balance`:
  - Excluir `accounts.type = credit_card`
  - Respeitar `accounts.incluir_soma` (inclui `NULL` como `true` por compatibilidade)
  - Ignorar `accounts.is_archived = true`
- Recálculo automático de saldos (`RecalculateAccountBalances`) agora não altera cartões de crédito.

Arquivos alterados neste lote:
- `app/Models/Account.php` (scopes `cashLike()` e `includedInNetWorth()`)
- `app/Jobs/RecalculateAccountBalances.php`
- `app/Services/ProjecaoService.php`
- `app/Http/Controllers/WidgetController.php`

