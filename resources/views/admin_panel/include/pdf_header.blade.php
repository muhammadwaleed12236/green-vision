<div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
    @if($appSettings['company_logo'])
        <img src="{{ base64storage($appSettings['company_logo']) }}" alt="{{ $appSettings['company_name'] }}" style="max-height: 80px;">
    @endif
    <h2 style="margin: 5px 0;">{{ $appSettings['company_name'] }}</h2>
    <p style="margin: 2px 0;">{{ $appSettings['company_address'] }}</p>
    <p style="margin: 2px 0;">Phone: {{ $appSettings['company_phone'] }}</p>
</div>
