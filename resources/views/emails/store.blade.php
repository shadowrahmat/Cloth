<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#F7F5F0;font-family:'Hind Siliguri','Noto Sans Bengali',Arial,sans-serif;color:#111111;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F5F0;">
  <tr>
    <td align="center" style="padding:32px 12px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
        {{-- Logo / store name --}}
        <tr>
          <td align="center" style="padding:0 0 24px;">
            <a href="{{ $store['store_url'] ?? '#' }}" style="text-decoration:none;color:#111111;">
              @if (!empty($store['__logo']))
                <img src="{{ $store['__logo'] }}" alt="{{ $store['store_name'] ?? '' }}" height="44" style="height:44px;width:auto;border:0;display:block;margin:0 auto;">
              @else
                <span style="font-family:Georgia,serif;font-size:24px;letter-spacing:3px;">{{ $store['store_name'] ?? '' }}</span>
              @endif
            </a>
          </td>
        </tr>

        {{-- Card --}}
        <tr>
          <td style="background:#FFFFFF;border-radius:16px;border:1px solid #E8E1D6;padding:36px 32px;">
            @if ($heading !== '')
              <h1 style="margin:0 0 20px;font-family:Georgia,'Hind Siliguri',serif;font-size:24px;line-height:1.35;font-weight:600;color:#111111;">{{ $heading }}</h1>
              <div style="width:40px;height:3px;background:#C5A880;border-radius:2px;margin:0 0 24px;"></div>
            @endif

            {!! $bodyHtml !!}

            @if ($buttonLabel !== '' && $buttonUrl !== '')
              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:8px 0 4px;">
                <tr>
                  <td style="background:#111111;border-radius:10px;">
                    <a href="{{ $buttonUrl }}" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:600;color:#FFFFFF;text-decoration:none;">{{ $buttonLabel }}</a>
                  </td>
                </tr>
              </table>
            @endif
          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td align="center" style="padding:24px 16px 0;font-size:12px;line-height:1.7;color:#69736E;">
            <strong style="color:#111111;">{{ $store['store_name'] ?? '' }}</strong><br>
            @if (!empty($store['__address'])){!! nl2br(e($store['__address']), false) !!}<br>@endif
            @if (!empty($store['store_phone'])){{ $store['store_phone'] }}@endif
            @if (!empty($store['store_phone']) && !empty($store['store_email'])) · @endif
            @if (!empty($store['store_email']))<a href="mailto:{{ $store['store_email'] }}" style="color:#69736E;">{{ $store['store_email'] }}</a>@endif
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
