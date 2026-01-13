#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

SSH_HOST="${SSH_HOST:-}"
SSH_USER="${SSH_USER:-}"
SSH_PORT="${SSH_PORT:-22}"
SSH_KEY="${SSH_KEY:-${HOME}/.ssh/kitamo_deploy}"
PROJECT_DIR="${PROJECT_DIR:-~/domains/kitamo.com.br/public_html}"

if [[ -z "${SSH_HOST}" || -z "${SSH_USER}" ]]; then
  echo "Uso:"
  echo "  SSH_HOST=... SSH_USER=... [SSH_PORT=22] [PROJECT_DIR=~/domains/kitamo.com.br/public_html] scripts/hostinger-deploy-ssh.sh"
  echo ""
  echo "Exemplo:"
  echo "  SSH_HOST=185.xxx.xxx.xxx SSH_USER=u123456 SSH_PORT=65002 scripts/hostinger-deploy-ssh.sh"
  exit 2
fi

if ! command -v ssh >/dev/null 2>&1 || ! command -v scp >/dev/null 2>&1; then
  echo "ssh/scp não encontrados. Rode este script no WSL/Linux/Git Bash." >&2
  exit 1
fi

echo "==> Gerando pacote Hostinger (zip)"
scripts/hostinger-package.sh

zip_path="$(ls -t kitamo-hostinger-backup-*.zip | head -n 1)"
remote="${SSH_USER}@${SSH_HOST}"
remote_zip="${PROJECT_DIR}/__deploy.zip"

echo "==> Upload do zip: ${zip_path} -> ${remote}:${remote_zip}"
if [[ -f "${SSH_KEY}" ]]; then
  scp -i "${SSH_KEY}" -P "${SSH_PORT}" "${zip_path}" "${remote}:${remote_zip}"
else
  scp -P "${SSH_PORT}" "${zip_path}" "${remote}:${remote_zip}"
fi

echo "==> Deploy remoto (sem sobrescrever .env / storage)"
if [[ -f "${SSH_KEY}" ]]; then
  ssh -i "${SSH_KEY}" -p "${SSH_PORT}" "${remote}" "bash -lc '
set -euo pipefail
cd \"${PROJECT_DIR}\"

echo \"PWD: \$(pwd)\"
test -f artisan || { echo \"ERRO: artisan não encontrado em ${PROJECT_DIR}\"; exit 10; }
test -d public || { echo \"ERRO: pasta public não encontrada em ${PROJECT_DIR}\"; exit 10; }

tmp=\"\$(mktemp -d /tmp/kitamo_deploy.XXXXXX)\"
cleanup() { rm -rf \"\$tmp\" \"__deploy.zip\"; }
trap cleanup EXIT

if command -v unzip >/dev/null 2>&1; then
  unzip -q \"__deploy.zip\" -d \"\$tmp\"
elif command -v python3 >/dev/null 2>&1; then
  python3 -m zipfile -e \"__deploy.zip\" \"\$tmp\"
else
  echo \"ERRO: precisa de unzip ou python3 no servidor para extrair o zip\"
  exit 11
fi

if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete-after --delay-updates \\
    --exclude \".env\" \\
    --exclude \"storage/\" \\
    \"\$tmp/\" \"./\"
else
  echo \"AVISO: rsync não encontrado no servidor; fazendo replace por diretórios (mantendo .env e storage)\"
  for p in app bootstrap config database public resources routes vendor artisan composer.json composer.lock package.json package-lock.json vite.config.* postcss.config.* tailwind.config.* tsconfig.*; do
    if [ -e \"\$tmp/\$p\" ]; then
      rm -rf \"./\$p\"
      cp -a \"\$tmp/\$p\" \"./\$p\"
    fi
  done
fi

mkdir -p storage bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

rm -f public/hot || true

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executar script de pós-deploy (adapta estrutura para Hostinger)
if [ -f scripts/hostinger-post-deploy.sh ]; then
  bash scripts/hostinger-post-deploy.sh
fi

echo \"OK: deploy finalizado\"
'"
else
  ssh -p "${SSH_PORT}" "${remote}" "bash -lc '
set -euo pipefail
cd \"${PROJECT_DIR}\"

echo \"PWD: \$(pwd)\"
test -f artisan || { echo \"ERRO: artisan não encontrado em ${PROJECT_DIR}\"; exit 10; }
test -d public || { echo \"ERRO: pasta public não encontrada em ${PROJECT_DIR}\"; exit 10; }

tmp=\"\$(mktemp -d /tmp/kitamo_deploy.XXXXXX)\"
cleanup() { rm -rf \"\$tmp\" \"__deploy.zip\"; }
trap cleanup EXIT

if command -v unzip >/dev/null 2>&1; then
  unzip -q \"__deploy.zip\" -d \"\$tmp\"
elif command -v python3 >/dev/null 2>&1; then
  python3 -m zipfile -e \"__deploy.zip\" \"\$tmp\"
else
  echo \"ERRO: precisa de unzip ou python3 no servidor para extrair o zip\"
  exit 11
fi

if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete-after --delay-updates \
    --exclude \".env\" \
    --exclude \"storage/\" \
    \"\$tmp/\" \"./\"
else
  echo \"AVISO: rsync não encontrado no servidor; fazendo replace por diretórios (mantendo .env e storage)\"
  for p in app bootstrap config database public resources routes vendor artisan composer.json composer.lock package.json package-lock.json vite.config.* postcss.config.* tailwind.config.* tsconfig.*; do
    if [ -e \"\$tmp/\$p\" ]; then
      rm -rf \"./\$p\"
      cp -a \"\$tmp/\$p\" \"./\$p\"
    fi
  done
fi

mkdir -p storage bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

rm -f public/hot || true

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executar script de pós-deploy (adapta estrutura para Hostinger)
if [ -f scripts/hostinger-post-deploy.sh ]; then
  bash scripts/hostinger-post-deploy.sh
fi

echo \"OK: deploy finalizado\"
'"
fi

echo "OK"
