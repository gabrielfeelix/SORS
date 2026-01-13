# Deploy Hostinger (KITAMO)

Projeto no servidor:

- `~/domains/kitamo.com.br/public_html`

## Deploy recomendado (automatizado)

1) Gere o pacote e faça deploy via SSH (não sobrescreve `.env` nem `storage/`):

```bash
SSH_HOST=SEU_HOST SSH_USER=SEU_USER SSH_PORT=65002 PROJECT_DIR=~/domains/kitamo.com.br/public_html \
  scripts/hostinger-deploy-ssh.sh
```

O script:
- roda `npm run build`
- monta um zip com `vendor/` (produção) e `public/build`
- sobe `__deploy.zip` no servidor
- extrai e sincroniza, preservando `.env` e `storage/`
- roda `php artisan optimize:clear` + caches

## Checklist manual (se precisar)

0) Entrar e ir para a pasta:

```bash
cd ~/domains/kitamo.com.br/public_html
```

1) Conferir:

```bash
ls -la
```

2) Atualizar código:

- Se existir `.git`, pode usar `git pull`:

```bash
ls -la | grep .git
git status
git pull origin main
```

Sem `.git`: faça upload via File Manager/SFTP (não sobrescreva `.env`).

3) Composer (se houver composer.json/lock alterado ou erro de autoload):

```bash
composer install --no-dev --optimize-autoloader
```

4) Vite build:

- Se tiver Node no servidor:

```bash
node -v
npm -v
npm ci
npm run build
ls -la public/build
ls -la public/build/assets | head -n 20
```

- Se NÃO tiver Node:
  - rode `npm run build` localmente e suba `public/build/` inteiro.

5) Migrations (se mudar `database/migrations/`):

```bash
php artisan migrate
```

6) Limpar/recachear:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

7) Permissões (se erro de escrita/cache/log):

```bash
chmod -R 775 storage bootstrap/cache
```

## `.env` no servidor

O repositório inclui `.env.example`. No servidor, crie/edite o `.env` real (produção) a partir dele.

