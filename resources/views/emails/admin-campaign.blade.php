<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $campaign->title }}</title>
  </head>
  <body style="margin:0; padding:0; background:#f8fafc; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif;">
    <div style="max-width:640px; margin:0 auto; padding:24px;">
      <div style="background:#ffffff; border-radius:16px; padding:24px; border:1px solid #e2e8f0;">
        <div style="font-size:20px; font-weight:700; color:#0f172a; margin-bottom:12px;">
          {{ $campaign->title }}
        </div>
        @if($campaign->content)
          <div style="font-size:14px; line-height:1.6; color:#334155; white-space:pre-wrap;">
            {!! nl2br(e($campaign->content)) !!}
          </div>
        @else
          <div style="font-size:14px; line-height:1.6; color:#64748b;">
            (Sem conteúdo)
          </div>
        @endif
      </div>
      <div style="text-align:center; font-size:12px; color:#94a3b8; margin-top:16px;">
        Enviado via Kitamo
      </div>
    </div>
  </body>
</html>

