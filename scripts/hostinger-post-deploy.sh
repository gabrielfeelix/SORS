#!/usr/bin/env bash
# Script executado automaticamente no servidor após o deploy
# Adapta a estrutura Laravel para hospedagem compartilhada Hostinger

set -euo pipefail

echo "==> Adaptando estrutura para Hostinger..."

# Copiar assets de public/build para raiz (somente se não existir na raiz ainda)
if [ -d "public/build" ] && [ ! -d "build" ]; then
    echo "  - Copiando build/ de public/ para raiz (primeira vez)"
    cp -r public/build ./build
    chmod -R 755 build/
elif [ -d "public/build" ]; then
    echo "  - Sincronizando build/ de public/ para raiz"
    rsync -a --delete public/build/ ./build/
    chmod -R 755 build/
fi

# Copiar arquivos públicos para raiz
echo "  - Copiando arquivos públicos"
cp -f public/.htaccess ./.htaccess 2>/dev/null || true
cp -f public/favicon.* ./ 2>/dev/null || true
cp -f public/robots.txt ./ 2>/dev/null || true

# Criar index.php na raiz (adaptado)
echo "  - Criando index.php na raiz"
cat > index.php << 'INDEXPHP'
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
INDEXPHP

# Ajustar permissões
echo "  - Ajustando permissões"
chmod 755 .
chmod 644 index.php .htaccess 2>/dev/null || true
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

echo "==> Estrutura adaptada com sucesso!"
