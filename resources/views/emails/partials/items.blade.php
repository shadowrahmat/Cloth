<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:4px 0 20px;border:1px solid #EFE9DF;border-radius:12px;border-collapse:separate;overflow:hidden;">
  @foreach ($items as $item)
    <tr>
      <td style="padding:14px 16px;border-bottom:1px solid #EFE9DF;font-size:14px;line-height:1.5;color:#111111;">
        <strong>{{ $item['name'] }}</strong>
        @if ($item['meta'] !== '')<br><span style="font-size:12px;color:#69736E;">{{ $item['meta'] }}</span>@endif
        <br><span style="font-size:12px;color:#69736E;">{{ \App\Services\StoreMailer::bnDigits((string) $item['qty']) }} × {{ $item['price'] }}</span>
      </td>
      <td align="right" valign="top" style="padding:14px 16px;border-bottom:1px solid #EFE9DF;font-size:14px;color:#111111;white-space:nowrap;">{{ $item['total'] }}</td>
    </tr>
  @endforeach
  @foreach ($totals as $row)
    <tr>
      <td style="padding:{{ !empty($row[2]) ? '14px' : '8px' }} 16px;font-size:{{ !empty($row[2]) ? '15px' : '13px' }};color:{{ !empty($row[2]) ? '#111111' : '#69736E' }};{{ !empty($row[2]) ? 'font-weight:700;background:#F7F5F0;' : '' }}">{{ $row[0] }}</td>
      <td align="right" style="padding:{{ !empty($row[2]) ? '14px' : '8px' }} 16px;font-size:{{ !empty($row[2]) ? '15px' : '13px' }};color:#111111;white-space:nowrap;{{ !empty($row[2]) ? 'font-weight:700;background:#F7F5F0;' : '' }}">{{ $row[1] }}</td>
    </tr>
  @endforeach
</table>
