# Configuração Google OAuth - KITAMO

## Problema CORS

O erro "Access-Control-Allow-Origin" que você está vendo acontece porque o Google OAuth precisa ter os URIs de redirecionamento configurados corretamente no Google Cloud Console.

## Passos para Configurar

### 1. Acesse o Google Cloud Console

Vá para: https://console.cloud.google.com/

### 2. Selecione ou Crie um Projeto

- Se já tiver um projeto para o KITAMO, selecione-o
- Caso contrário, crie um novo projeto chamado "KITAMO"

### 3. Ative a Google+ API

1. No menu lateral, vá em **APIs & Services** > **Library**
2. Procure por "Google+ API"
3. Clique em **Enable**

### 4. Configure a Tela de Consentimento OAuth

1. Vá em **APIs & Services** > **OAuth consent screen**
2. Selecione **External** (a menos que tenha Google Workspace)
3. Preencha:
   - **App name**: KITAMO
   - **User support email**: seu email
   - **Developer contact email**: seu email
4. Clique em **Save and Continue**
5. Em **Scopes**, adicione:
   - `.../auth/userinfo.email`
   - `.../auth/userinfo.profile`
6. Continue até o final

### 5. Crie as Credenciais OAuth

1. Vá em **APIs & Services** > **Credentials**
2. Clique em **Create Credentials** > **OAuth client ID**
3. Selecione **Web application**
4. Configure:
   - **Name**: KITAMO Web Client
   - **Authorized JavaScript origins**:
     - `https://kitamo.com.br`
     - `http://localhost:8000` (para desenvolvimento local)
   - **Authorized redirect URIs**:
     - `https://kitamo.com.br/auth/google/callback`
     - `http://localhost:8000/auth/google/callback` (para desenvolvimento local)

### 6. Copie as Credenciais

Após criar, você receberá:
- **Client ID**: algo como `123456789-abc123.apps.googleusercontent.com`
- **Client Secret**: algo como `GOCSPX-abc123def456`

### 7. Configure o .env no Servidor

SSH no servidor Hostinger e edite o arquivo `.env`:

```bash
ssh -i ~/.ssh/kitamo_deploy -p 65002 u626119115@147.79.84.203
cd domains/kitamo.com.br/public_html
nano .env
```

Adicione estas linhas ao `.env`:

```env
GOOGLE_CLIENT_ID=seu_client_id_aqui
GOOGLE_CLIENT_SECRET=seu_client_secret_aqui
GOOGLE_REDIRECT_URI=https://kitamo.com.br/auth/google/callback
```

### 8. Limpe o Cache no Servidor

Ainda via SSH:

```bash
php artisan config:cache
php artisan route:cache
```

### 9. Teste

Acesse `https://kitamo.com.br/login` e clique no botão "Continuar com Google".

## URIs Importantes

Para referência, os URIs que devem estar configurados no Google Cloud Console:

### JavaScript Origins
- `https://kitamo.com.br`

### Redirect URIs
- `https://kitamo.com.br/auth/google/callback`

## Solução de Problemas

### Erro: "redirect_uri_mismatch"
- Verifique se o redirect URI no Google Console está **exatamente** igual ao configurado no `.env`
- Lembre-se: `https://kitamo.com.br/auth/google/callback` (com `/auth/google/callback` no final)

### Erro: "Access blocked: This app's request is invalid"
- Complete a configuração da tela de consentimento OAuth
- Adicione seu email como usuário de teste (se estiver em modo Testing)

### CORS Error
- Verifique se `https://kitamo.com.br` está nos "Authorized JavaScript origins"
- Aguarde alguns minutos após alterar as configurações (propagação pode demorar)

## Modo de Desenvolvimento Local

Para testar localmente, adicione também no Google Console:

**JavaScript Origins:**
- `http://localhost:8000`

**Redirect URIs:**
- `http://localhost:8000/auth/google/callback`

E no `.env` local:
```env
GOOGLE_CLIENT_ID=mesmo_client_id
GOOGLE_CLIENT_SECRET=mesmo_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```
