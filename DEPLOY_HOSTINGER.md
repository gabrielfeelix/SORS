# Deploy Hostinger (KITAMO)

Projeto no servidor:

- `~/domains/kitamo.com.br/public_html`

## Credenciais SSH

```bash
SSH_HOST=147.79.84.203
SSH_USER=u626119115
SSH_PORT=65002
PROJECT_DIR=~/domains/kitamo.com.br/public_html
```

Chave SSH: `~/.ssh/kitamo_deploy`

## Deploy recomendado (automatizado) ⭐

**Este é o método principal e mais seguro!**

Execute o comando:

```bash
SSH_HOST=147.79.84.203 SSH_USER=u626119115 SSH_PORT=65002 PROJECT_DIR=~/domains/kitamo.com.br/public_html \
  scripts/hostinger-deploy-ssh.sh
```

### O que o script faz:

1. ✅ Roda `npm run build` localmente
2. ✅ Roda `composer install --no-dev --optimize-autoloader` em staging
3. ✅ Monta um zip com código + `vendor/` (produção) + `public/build`
4. ✅ Sobe `__deploy.zip` no servidor via SCP
5. ✅ Extrai e sincroniza, **preservando `.env` e `storage/`**
6. ✅ Roda comandos do artisan:
   - `php artisan optimize:clear`
   - `php artisan config:cache`
   - `php artisan route:cache`
   - `php artisan view:cache`

### Notas importantes:

- **NÃO sobrescreve** `.env` nem `storage/` (banco de dados, uploads, logs ficam intactos)
- Requer chave SSH configurada em `~/.ssh/kitamo_deploy`
- O build do frontend é feito **localmente** (não precisa de Node no servidor)
- Conexões SSH podem cair por timeout em comandos longos - o script é otimizado para evitar isso

## Deploy alternativo: SSH direto + Git Pull

**Só use se o servidor tiver repositório git configurado em `public_html/.git`**

```bash
ssh -p 65002 u626119115@147.79.84.203 "cd ~/domains/kitamo.com.br/public_html && git pull origin main && php artisan optimize:clear && php artisan config:cache && php artisan migrate --force"
```

⚠️ **Atenção:** Este método NÃO faz build do frontend. Você precisará:
- Fazer `npm run build` localmente antes
- Commitar os arquivos de `public/build/` no git, OU
- Subir manualmente via SFTP

## Troubleshooting

### Erro: "Connection timeout" ou "Exit code 255"

Conexões SSH da Hostinger podem cair em comandos longos. Soluções:

1. **Use o script automatizado** (recomendado) - ele é otimizado para evitar timeouts
2. Execute comandos curtos individualmente
3. Use um script temporário:

```bash
# Criar script local
cat > /tmp/artisan_commands.sh << 'EOF'
#!/bin/bash
cd ~/domains/kitamo.com.br/public_html
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Comandos concluídos"
EOF

# Enviar e executar
chmod +x /tmp/artisan_commands.sh
scp -i ~/.ssh/kitamo_deploy -P 65002 /tmp/artisan_commands.sh u626119115@147.79.84.203:/tmp/
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 'bash /tmp/artisan_commands.sh && rm /tmp/artisan_commands.sh'
```

### Erro: "Permission denied" em storage/ ou bootstrap/cache

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "cd ~/domains/kitamo.com.br/public_html && chmod -R 775 storage bootstrap/cache"
```

### Verificar se deploy funcionou

```bash
# Verificar arquivos atualizados
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "ls -lt ~/domains/kitamo.com.br/public_html/ | head -15"

# Verificar build assets
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "ls -lt ~/domains/kitamo.com.br/public_html/public/build/ | head -10"

# Verificar versão do Laravel
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "cd ~/domains/kitamo.com.br/public_html && php artisan --version"
```

## Checklist pós-deploy

Após cada deploy, **sempre** execute:

- [ ] Verificar se o site está no ar: https://kitamo.com.br
- [ ] Testar login/funcionalidades principais
- [ ] Verificar console do browser (F12) por erros JS
- [ ] Se houver migrations: confirmar que rodaram com `php artisan migrate:status`

## Migrations no servidor

Se houver mudanças em `database/migrations/`:

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "cd ~/domains/kitamo.com.br/public_html && php artisan migrate --force"
```

O `--force` é necessário em produção (sem ele, o artisan pede confirmação interativa).

## Configuração inicial do SSH (primeira vez)

### 1. Configurar chave SSH

Se ainda não tiver a chave `kitamo_deploy`, gere uma:

```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/kitamo_deploy -C "kitamo-deploy"
```

### 2. Adicionar chave pública no servidor

Copie a chave pública para o servidor:

```bash
ssh-copy-id -i ~/.ssh/kitamo_deploy.pub -p 65002 u626119115@147.79.84.203
```

Ou manualmente via hPanel:
1. Acesse https://hpanel.hostinger.com/
2. Vá em "Avançado" → "SSH Access"
3. Cole o conteúdo de `~/.ssh/kitamo_deploy.pub`

### 3. Configurar SSH config (opcional, mas recomendado)

Crie/edite `~/.ssh/config`:

```bash
Host hostinger-kitamo
    HostName 147.79.84.203
    User u626119115
    Port 65002
    IdentityFile ~/.ssh/kitamo_deploy
    ServerAliveInterval 60
    ServerAliveCountMax 3
```

Depois você pode conectar simplesmente com:

```bash
ssh hostinger-kitamo
```

## `.env` no servidor

O repositório inclui `.env.example`. No servidor, crie/edite o `.env` real (produção) a partir dele.

**Importante:** O script de deploy **NÃO sobrescreve** o `.env` existente no servidor.

### Verificar/editar .env no servidor:

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "cd ~/domains/kitamo.com.br/public_html && cat .env"
```

## Dicas úteis

### Conectar ao servidor rapidamente

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203
```

### Ver logs do Laravel

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "tail -f ~/domains/kitamo.com.br/public_html/storage/logs/laravel.log"
```

### Limpar cache manualmente

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "cd ~/domains/kitamo.com.br/public_html && php artisan cache:clear && php artisan view:clear && php artisan config:clear"
```

### Espaço em disco

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "df -h | grep domains"
```

## Rollback (reverter deploy)

Se algo der errado após o deploy, você pode reverter:

1. **Via Git (se usar git pull):**

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203 "cd ~/domains/kitamo.com.br/public_html && git log --oneline -5 && git reset --hard COMMIT_HASH && php artisan optimize:clear"
```

2. **Via backup manual:**
   - O script gera um arquivo `.zip` local (em `kitamo-hostinger-backup-*.zip`)
   - Você pode guardar esses zips como backup
   - Para restaurar: suba o zip antigo e extraia manualmente

