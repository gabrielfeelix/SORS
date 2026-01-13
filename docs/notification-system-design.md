# Sistema de Notificações Inteligentes - KITAMO

## Visão Geral

Sistema de notificações proativas e inteligentes que ajudam os usuários a manter controle financeiro através de alertas contextuais, lembretes e insights personalizados.

## Tipos de Notificações

### 1. Alertas de Saldo (Contas e Cartões)

#### 1.1 Saldo Baixo
**Trigger:** `current_balance < R$100` (ou valor configurável pelo usuário)
**Quando:** Verificado hourly (junto com RecalculateAccountBalances)
**Mensagem:** "⚠️ Saldo baixo na conta [Nome]: R$XX,XX. Considere transferir fundos."
**Tipo:** warning
**Canal:** in-app + email (opcional)

#### 1.2 Saldo Negativo
**Trigger:** `current_balance < 0`
**Quando:** Imediato (após transação)
**Mensagem:** "🚨 Sua conta [Nome] está negativa: R$XX,XX"
**Tipo:** critical
**Canal:** in-app + email + SMS (se disponível)

#### 1.3 Limite de Crédito (80%)
**Trigger:** Cartão de crédito com `current_balance > (credit_limit * 0.8)`
**Quando:** Verificado hourly
**Mensagem:** "💳 Você já usou 80% do limite do cartão [Nome] (R$XX/R$YY)"
**Tipo:** warning
**Canal:** in-app + email

#### 1.4 Limite Estourado
**Trigger:** `current_balance > credit_limit`
**Quando:** Imediato
**Mensagem:** "🚨 Limite do cartão [Nome] ultrapassado! R$XX,XX de R$YY,YY"
**Tipo:** critical
**Canal:** in-app + email

### 2. Lembretes de Pagamento (Cartões de Crédito)

#### 2.1 Fechamento da Fatura
**Trigger:** 2 dias antes de `closing_day`
**Quando:** Daily (02:00 AM)
**Mensagem:** "📅 Sua fatura do cartão [Nome] fecha em 2 dias. Saldo atual: R$XX,XX"
**Tipo:** info
**Canal:** in-app

#### 2.2 Vencimento da Fatura (7 dias)
**Trigger:** 7 dias antes de `due_day`
**Quando:** Daily (02:00 AM)
**Mensagem:** "💰 Fatura do cartão [Nome] vence em 7 dias (R$XX,XX)"
**Tipo:** info
**Canal:** in-app

#### 2.3 Vencimento Próximo (3 dias)
**Trigger:** 3 dias antes de `due_day`
**Quando:** Daily (02:00 AM)
**Mensagem:** "⏰ Fatura do cartão [Nome] vence em 3 dias! Valor: R$XX,XX"
**Tipo:** warning
**Canal:** in-app + email

#### 2.4 Vencimento Amanhã
**Trigger:** 1 dia antes de `due_day`
**Quando:** Daily (02:00 AM)
**Mensagem:** "🚨 Última chance! Fatura do cartão [Nome] vence amanhã (R$XX,XX)"
**Tipo:** critical
**Canal:** in-app + email + SMS

#### 2.5 Fatura Vencida
**Trigger:** Data atual > `due_day` e `current_balance > 0`
**Quando:** Daily (02:00 AM)
**Mensagem:** "⚠️ Fatura do cartão [Nome] está vencida! R$XX,XX + possíveis juros"
**Tipo:** critical
**Canal:** in-app + email

### 3. Metas (Progresso e Prazos)

#### 3.1 Prazo Próximo (7 dias)
**Trigger:** 7 dias antes de `due_date`
**Quando:** Daily (02:00 AM)
**Mensagem:** "🎯 Sua meta '[Nome]' vence em 7 dias. Progresso: XX% (R$YY de R$ZZ)"
**Tipo:** info
**Canal:** in-app

#### 3.2 Prazo Iminente (3 dias)
**Trigger:** 3 dias antes de `due_date`
**Quando:** Daily (02:00 AM)
**Mensagem:** "⏰ Faltam apenas 3 dias para sua meta '[Nome]'! Você está XX% lá"
**Tipo:** warning
**Canal:** in-app + email

#### 3.3 Último Dia
**Trigger:** 1 dia antes de `due_date`
**Quando:** Daily (02:00 AM)
**Mensagem:** "🚀 Último dia para sua meta '[Nome]'! Faltam R$XX para completar"
**Tipo:** warning
**Canal:** in-app + email

#### 3.4 Meta Atrasada
**Trigger:** `status = 'late'` (calculado pelo SyncGoalProgress job)
**Quando:** Daily (02:30 AM)
**Mensagem:** "📉 Sua meta '[Nome]' está atrasada. Progresso: XX%. Considere ajustar o prazo?"
**Tipo:** warning
**Canal:** in-app

#### 3.5 Marcos de Progresso (25%, 50%, 75%)
**Trigger:** `(current_amount / target_amount) * 100` atinge milestone
**Quando:** Imediato (após depósito)
**Mensagem 25%:** "🎉 Você chegou a 25% da meta '[Nome]'! Continue assim!"
**Mensagem 50%:** "🌟 Meio caminho andado! 50% da meta '[Nome]' alcançados!"
**Mensagem 75%:** "🔥 Você está quase lá! 75% da meta '[Nome]' completos!"
**Tipo:** success
**Canal:** in-app

#### 3.6 Meta Concluída
**Trigger:** `current_amount >= target_amount`
**Quando:** Imediato (após depósito)
**Mensagem:** "🏆 Parabéns! Você atingiu a meta '[Nome]'! R$XX,XX alcançados!"
**Tipo:** success
**Canal:** in-app + email

#### 3.7 Meta Estagnada
**Trigger:** Nenhum depósito nos últimos 30 dias
**Quando:** Daily (02:30 AM)
**Mensagem:** "⏸️ Sua meta '[Nome]' não recebe depósitos há 30 dias. Que tal contribuir hoje?"
**Tipo:** info
**Canal:** in-app

#### 3.8 Sugestão de Contribuição
**Trigger:** Meta com prazo, calculando valor necessário para atingir no tempo
**Quando:** Weekly (segunda-feira, 09:00 AM)
**Mensagem:** "💡 Para atingir '[Nome]' no prazo, você precisa depositar R$XX por semana"
**Tipo:** info
**Canal:** in-app

### 4. Transações Recorrentes

#### 4.1 Despesa Recorrente Próxima
**Trigger:** 3 dias antes de `next_run_at` (kind = 'expense')
**Quando:** Daily (02:00 AM)
**Mensagem:** "📅 Lembrete: Despesa recorrente '[Descrição]' (R$XX) será debitada em 3 dias"
**Tipo:** info
**Canal:** in-app

#### 4.2 Receita Esperada
**Trigger:** Dia de `next_run_at` (kind = 'income')
**Quando:** Daily (02:00 AM)
**Mensagem:** "💵 Receita recorrente '[Descrição]' (R$XX) será creditada hoje"
**Tipo:** success
**Canal:** in-app

#### 4.3 Renovação de Assinatura
**Trigger:** `frequency = 'monthly'` e 7 dias antes de `next_run_at`
**Quando:** Daily (02:00 AM)
**Mensagem:** "🔄 Assinatura '[Descrição]' será renovada em 7 dias (R$XX)"
**Tipo:** info
**Canal:** in-app

#### 4.4 Transação Recorrente Criada
**Trigger:** Após ApplyRecurringTransactions job criar transação
**Quando:** Daily (após job 02:00 AM)
**Mensagem:** "✅ Transação recorrente '[Descrição]' (R$XX) foi registrada automaticamente"
**Tipo:** info
**Canal:** in-app

#### 4.5 Saldo Insuficiente para Recorrente
**Trigger:** Saldo de conta < valor de despesa recorrente próxima (3 dias)
**Quando:** Daily (02:00 AM)
**Mensagem:** "⚠️ Saldo insuficiente para despesa recorrente '[Descrição]' (R$XX) em 3 dias"
**Tipo:** warning
**Canal:** in-app + email

### 5. Orçamento e Gastos (Insights)

#### 5.1 Limite de Categoria Atingido (80%)
**Trigger:** Gastos de categoria ≥ 80% do orçamento mensal definido
**Quando:** Imediato (após transação)
**Mensagem:** "📊 Você já gastou R$XX em [Categoria] este mês (80% do orçamento)"
**Tipo:** warning
**Canal:** in-app

#### 5.2 Orçamento de Categoria Estourado
**Trigger:** Gastos de categoria > orçamento mensal
**Quando:** Imediato (após transação)
**Mensagem:** "🚨 Você ultrapassou o orçamento de [Categoria]! R$XX de R$YY"
**Tipo:** critical
**Canal:** in-app + email

#### 5.3 Gasto Incomum Detectado
**Trigger:** Transação > 150% da média da categoria (últimos 3 meses)
**Quando:** Imediato (após transação)
**Mensagem:** "🔍 Gasto incomum detectado: R$XX em [Categoria] (média: R$YY)"
**Tipo:** info
**Canal:** in-app

#### 5.4 Resumo Diário de Gastos
**Trigger:** Fim do dia com transações
**Quando:** Daily (21:00)
**Mensagem:** "📝 Resumo do dia: XX transações, R$YY gastos, R$ZZ recebidos"
**Tipo:** info
**Canal:** in-app

#### 5.5 Streak de Economia
**Trigger:** N dias consecutivos sem despesas (ou abaixo da média)
**Quando:** Daily (após verificação)
**Mensagem:** "🏅 Parabéns! 5 dias sem gastos desnecessários. Continue assim!"
**Tipo:** success
**Canal:** in-app

#### 5.6 Receita Recebida
**Trigger:** Transação com `kind = 'income'` e status muda para 'received'
**Quando:** Imediato
**Mensagem:** "💰 Receita '[Descrição]' foi recebida: R$XX"
**Tipo:** success
**Canal:** in-app

### 6. Resumos Periódicos

#### 6.1 Resumo Semanal
**Trigger:** Segunda-feira, 09:00
**Quando:** Weekly
**Mensagem:**
```
📊 Resumo da semana passada:
• Receitas: R$XXX
• Despesas: R$YYY
• Saldo: R$ZZZ
• Categoria mais gasta: [Nome] (R$AAA)
• Metas: N ativas, M próximas do prazo
```
**Tipo:** info
**Canal:** in-app + email (opcional)

#### 6.2 Resumo Mensal
**Trigger:** Primeiro dia do mês, 09:00
**Quando:** Monthly
**Mensagem:**
```
📈 Balanço de [Mês]:
• Total recebido: R$XXX
• Total gasto: R$YYY
• Economia: R$ZZZ
• Categoria top: [Nome] (R$AAA)
• Metas concluídas: N
• Taxa de atingimento de orçamento: XX%
```
**Tipo:** info
**Canal:** in-app + email

### 7. Previsões e Sugestões (IA/Smart)

#### 7.1 Previsão de Fluxo de Caixa
**Trigger:** Início da semana
**Quando:** Weekly (segunda-feira, 09:00)
**Mensagem:** "📊 Próximos 7 dias: R$XXX em despesas previstas. Saldo atual: R$YYY. Sobra estimada: R$ZZZ"
**Tipo:** info
**Canal:** in-app

#### 7.2 Sugestão de Alocação
**Trigger:** Saldo disponível alto + meta ativa
**Quando:** Weekly (sexta-feira, 18:00)
**Mensagem:** "💡 Você tem R$XXX disponível. Que tal alocar 20% (R$YY) para sua meta '[Nome]'?"
**Tipo:** info
**Canal:** in-app

#### 7.3 Alerta de Padrão de Gasto
**Trigger:** Análise de padrão (fins de semana, dias específicos)
**Quando:** Weekly
**Mensagem:** "📊 Insight: Você gasta 40% mais aos fins de semana (média: R$XX vs R$YY)"
**Tipo:** info
**Canal:** in-app

## Preferências de Notificação (User Settings)

Permitir ao usuário controlar:

```php
notification_preferences: [
  'low_balance_enabled' => true,
  'low_balance_threshold' => 100.00,
  'credit_card_reminders' => true,
  'goal_milestones' => true,
  'daily_summary' => false,
  'weekly_summary' => true,
  'monthly_summary' => true,
  'email_notifications' => ['critical', 'warning'],
  'sms_notifications' => ['critical'],
  'quiet_hours_start' => '22:00',
  'quiet_hours_end' => '08:00',
]
```

## Estrutura de Dados

### Notifications Table (Laravel padrão)
```php
- id (uuid)
- type (string) // NotificationClass
- notifiable_type (string)
- notifiable_id (bigint)
- data (json)
- read_at (timestamp, nullable)
- created_at, updated_at
```

### Notification Preferences Table
```php
- id (bigint)
- user_id (foreign key)
- category (string) // 'balance', 'credit_card', 'goals', etc.
- channels (json) // ['in_app', 'email', 'sms']
- enabled (boolean, default: true)
- threshold (decimal, nullable)
- created_at, updated_at
```

## Implementação Técnica

### 1. Jobs Schedulados
```php
// app/Jobs/Notifications/
- SendLowBalanceAlerts.php (hourly)
- SendCreditCardReminders.php (daily 02:00)
- SendGoalDeadlineReminders.php (daily 02:00)
- SendRecurringTransactionReminders.php (daily 02:00)
- SendDailySummary.php (daily 21:00)
- SendWeeklySummary.php (weekly Monday 09:00)
- SendMonthlySummary.php (monthly 1st 09:00)
```

### 2. Notification Classes
```php
// app/Notifications/
- LowBalanceNotification.php
- NegativeBalanceNotification.php
- CreditLimitWarningNotification.php
- CreditCardDueReminderNotification.php
- GoalMilestoneNotification.php
- GoalDeadlineReminderNotification.php
- RecurringTransactionReminderNotification.php
- BudgetExceededNotification.php
```

### 3. Event Listeners
```php
// app/Events/
- TransactionCreated
- GoalDepositMade
- AccountBalanceUpdated

// app/Listeners/
- CheckBudgetThresholds
- CheckGoalMilestones
- CheckCreditLimitWarning
```

## Canais de Notificação

### In-App (Database)
- Todas as notificações
- Armazenadas na tabela `notifications`
- Exibidas em um "notification center" (ícone de sino)

### Email
- Notificações `critical` e `warning` (configurável)
- Resumos semanais/mensais

### SMS (Futuro)
- Apenas notificações `critical`
- Requer integração com serviço SMS (Twilio, AWS SNS)

### Push (Futuro)
- Para app mobile
- Configurável por categoria

## Prioridades de Implementação

### Fase 1: Fundação (Semana 1)
- [ ] Migration de `notifications`
- [ ] Migration de `notification_preferences`
- [ ] Seeder para preferências padrão
- [ ] Trait NotificationPreferences no User model

### Fase 2: Alertas Críticos (Semana 2)
- [ ] Notificação: Saldo Baixo
- [ ] Notificação: Saldo Negativo
- [ ] Notificação: Limite de Crédito
- [ ] Notificação: Fatura Vencendo
- [ ] Job: SendLowBalanceAlerts
- [ ] Job: SendCreditCardReminders

### Fase 3: Metas e Orçamento (Semana 3)
- [ ] Notificação: Prazo de Meta Próximo
- [ ] Notificação: Marcos de Meta (25%, 50%, 75%, 100%)
- [ ] Notificação: Orçamento de Categoria
- [ ] Job: SendGoalDeadlineReminders
- [ ] Event: GoalMilestoneReached

### Fase 4: Recorrentes e Insights (Semana 4)
- [ ] Notificação: Transação Recorrente Próxima
- [ ] Notificação: Gasto Incomum
- [ ] Job: SendRecurringTransactionReminders
- [ ] Job: SendDailySummary

### Fase 5: Resumos e Sugestões (Semana 5)
- [ ] Job: SendWeeklySummary
- [ ] Job: SendMonthlySummary
- [ ] Algoritmo: Previsão de Fluxo de Caixa
- [ ] Algoritmo: Sugestões de Alocação

### Fase 6: UI/UX (Semana 6)
- [ ] Notification Center component (Vue)
- [ ] Página de Preferências de Notificação
- [ ] Badges de notificações não lidas
- [ ] Ações rápidas nas notificações ("Pagar fatura", "Depositar na meta")

## Exemplo de Código

### Notification Class
```php
// app/Notifications/LowBalanceNotification.php
<?php

namespace App\Notifications;

use App\Models\Account;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LowBalanceNotification extends Notification
{
    public function __construct(public Account $account) {}

    public function via($notifiable)
    {
        $prefs = $notifiable->notificationPreference('low_balance');
        return $prefs?->channels ?? ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'low_balance',
            'severity' => 'warning',
            'account_id' => $this->account->id,
            'account_name' => $this->account->name,
            'current_balance' => $this->account->current_balance,
            'message' => "Saldo baixo na conta {$this->account->name}: R$ {$this->account->current_balance}",
            'icon' => '⚠️',
            'action_url' => route('accounts.show', $this->account),
            'action_text' => 'Ver conta',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->warning()
            ->subject("⚠️ Saldo baixo na conta {$this->account->name}")
            ->line("Sua conta {$this->account->name} está com saldo baixo.")
            ->line("Saldo atual: R$ {$this->account->current_balance}")
            ->action('Ver conta', route('accounts.show', $this->account));
    }
}
```

### Job Example
```php
// app/Jobs/Notifications/SendLowBalanceAlerts.php
<?php

namespace App\Jobs\Notifications;

use App\Models\Account;
use App\Models\User;
use App\Notifications\LowBalanceNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class SendLowBalanceAlerts implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function handle()
    {
        $users = User::whereHas('accounts', function ($q) {
            $q->where('is_archived', false);
        })->get();

        foreach ($users as $user) {
            $threshold = $user->notificationPreference('low_balance')?->threshold ?? 100.00;

            $lowBalanceAccounts = $user->accounts()
                ->where('is_archived', false)
                ->where('current_balance', '<', $threshold)
                ->where('current_balance', '>', 0) // Not negative (different notification)
                ->get();

            foreach ($lowBalanceAccounts as $account) {
                $user->notify(new LowBalanceNotification($account));
            }
        }
    }
}
```

## Métricas e Analytics

Rastrear:
- Taxa de abertura de notificações
- Notificações mais/menos úteis (feedback do usuário)
- Taxa de ação (clique em "Ver conta", "Pagar fatura", etc.)
- Preferências mais comuns
- Horários de maior engajamento

## Considerações de Performance

- Usar jobs em fila (queue) para envio assíncrono
- Batch de notificações quando possível
- Cache de preferências de usuário
- Índices no banco para queries de agregação
- Limitar frequência de notificações do mesmo tipo (throttling)

## Testes

- Unit tests para cada Notification class
- Integration tests para jobs
- Feature tests para preferências de usuário
- E2E tests para fluxo completo de notificação
