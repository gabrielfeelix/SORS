# Guia de Correção - Erro 403 Forbidden no Hostinger

## Causas Comuns do Erro 403 em Laravel

1. **Document Root incorreto** - Apontando para a raiz do projeto em vez de `/public`
2. **Permissões de arquivos incorretas**
3. **Arquivo .htaccess ausente ou incorreto**
4. **Falta de arquivo index.php**

---

## ✅ CHECKLIST DE CORREÇÕES

### 1. Verificar Document Root
**CRÍTICO**: O Document Root DEVE apontar para a pasta `public/`

**Como configurar no Hostinger:**
1. Acesse: **hPanel → Websites → Seu site → Configurações**
2. Procure por: **"Document Root"** ou **"Website Root"**
3. Configure para: `/public_html/public` (ou `/domains/seudominio.com/public_html/public`)

**Estrutura correta:**
```
public_html/               ← Upload do projeto vai aqui
├── app/
├── bootstrap/
├── config/
├── public/               ← Document Root deve apontar aqui!
│   ├── index.php
│   ├── .htaccess
│   └── build/
├── resources/
├── storage/
├── vendor/
└── .env
```

---

### 2. Verificar Permissões de Pastas

**Via File Manager do Hostinger:**
1. Clique com botão direito nas pastas
2. Selecione "Permissions" ou "Permissões"

**Permissões necessárias:**
- **storage/** → 755 ou 775
- **storage/framework/** → 755 ou 775
- **storage/logs/** → 755 ou 775
- **bootstrap/cache/** → 755 ou 775
- **public/** → 755

**Via SSH (se tiver acesso):**
```bash
cd /home/usuario/public_html
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

### 3. Verificar Arquivo .htaccess

**Localização:** `public/.htaccess`

Deve conter este conteúdo:
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

### 4. Configurar Arquivo .env

**Criar/editar:** `.env` na raiz do projeto (mesmo nível de `artisan`)

**Configurações essenciais:**
```env
APP_NAME=KITAMO
APP_ENV=production
APP_KEY=base64:SUA_KEY_AQUI
APP_DEBUG=false
APP_URL=https://seudominio.com

# Database - Configurar com dados do Hostinger
DB_CONNECTION=mysql
DB_HOST=localhost  # ou o host fornecido pelo Hostinger
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_banco
DB_PASSWORD=senha_banco

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

**⚠️ Se APP_KEY estiver vazia:**
```bash
php artisan key:generate
```

---

### 5. Verificar index.php

**Localização:** `public/index.php`

Deve conter no início:
```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Verificar se estes caminhos estão corretos
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

---

### 6. Otimizações Laravel (Via SSH)

Se tiver acesso SSH:
```bash
cd /home/usuario/public_html

# Limpar caches antigos
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Criar caches de produção
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Otimizar autoloader
composer dump-autoload --optimize
```

---

### 7. Criar arquivo .htaccess na Raiz (Opcional mas Recomendado)

**Localização:** `.htaccess` na raiz do projeto (não em public/)

**Conteúdo:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Isso redireciona tudo para a pasta `public/` automaticamente.

---

## 🔍 DIAGNÓSTICO VIA SSH

Se tiver acesso SSH, execute:

```bash
# Ver permissões
ls -la storage/
ls -la bootstrap/cache/
ls -la public/

# Verificar se PHP está funcionando
php -v

# Testar artisan
php artisan --version

# Ver logs de erro
tail -n 50 storage/logs/laravel.log

# Ver qual Document Root está configurado
pwd
```

---

## 🚨 SOLUÇÃO MAIS COMUM

**90% dos casos é o Document Root:**

1. ✅ Certifique-se que todos os arquivos foram extraídos em `/public_html/`
2. ✅ Configure o Document Root para: `/public_html/public`
3. ✅ Aguarde 2-5 minutos para propagação
4. ✅ Limpe cache do navegador (Ctrl + Shift + R)

---

## 📝 Próximos Passos

1. **Verificar Document Root** (mais importante!)
2. **Corrigir permissões** de storage/ e bootstrap/cache/
3. **Configurar .env** com credenciais do banco
4. **Executar migrations**: `php artisan migrate --force`
5. **Testar o site**

---

## ❓ Ainda com problemas?

Verifique os logs de erro do servidor:
- **Hostinger**: hPanel → Advanced → Error Logs
- **Laravel**: `storage/logs/laravel.log`
