<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $sale->invoice_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300;400;500;600;700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Fira Sans', sans-serif;
            background-color: #fff;
            padding: 20px;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border: 1px solid #000;
        }

        .invoice-header {
            border-bottom: 3px solid #000;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-logo h1 {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            color: #000;
        }

        .company-logo p {
            font-size: 11px;
            margin: 5px 0 0 0;
            color: #666;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h2 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #000;
        }

        .invoice-title p {
            font-size: 12px;
            margin: 3px 0;
            color: #666;
        }

        .invoice-info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px 30px;
            border-bottom: 1px solid #ddd;
        }

        .info-block {
            padding: 15px;
            border: 1px solid #ddd;
        }

        .info-block-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .info-block p {
            font-size: 12px;
            margin: 5px 0;
            color: #333;
        }

        .info-block strong {
            color: #000;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .invoice-table thead {
            background-color: #000;
            color: white;
        }

        .invoice-table th {
            padding: 12px 10px;
            font-size: 12px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #000;
        }

        .invoice-table td {
            padding: 10px;
            font-size: 12px;
            border: 1px solid #ddd;
            color: #333;
        }

        .invoice-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .summary-section {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            padding: 20px 30px;
            border-top: 2px solid #000;
        }

        .summary-section.estimate-summary {
            display: flex;
            justify-content: flex-end;
            border-top: none;
        }

        .ledger-section {
            padding: 15px;
            border: 1px solid #ddd;
        }

        .ledger-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #000;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .ledger-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }

        .ledger-row:last-child {
            border-bottom: none;
            font-weight: bold;
            color: #000;
            border-top: 2px solid #000;
            margin-top: 5px;
            padding-top: 10px;
        }

        .totals-box {
            border: 2px solid #000;
            padding: 15px;
        }

        .summary-section.estimate-summary .totals-box {
            width: 100%;
            border: none;
            padding: 15px 0 0 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
        }

        .total-row.grand-total {
            border-top: 2px solid #000;
            margin-top: 10px;
            padding-top: 12px;
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .total-row.balance-due {
            font-weight: bold;
            color: #000;
            font-size: 14px;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            padding: 40px 30px 20px 30px;
            margin-top: 30px;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-bottom: 5px;
        }

        .signature-label {
            font-size: 11px;
            color: #666;
        }

        .invoice-footer {
            text-align: center;
            padding: 15px;
            background-color: #f5f5f5;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }

        .terms-note {
            font-size: 10px;
            color: #999;
            margin-top: 10px;
            font-style: italic;
        }

        .terms-conditions-block {
            margin: 20px 30px 0 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-left: 4px solid #000;
            background-color: #f9f9f9;
        }

        .terms-conditions-block h4 {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: #000;
            letter-spacing: 0.5px;
        }

        .terms-conditions-block ul {
            font-size: 11px;
            color: #444;
            padding-left: 20px;
            margin: 0;
            line-height: 1.6;
        }
        
        .terms-conditions-block ul li {
            margin-bottom: 4px;
        }

        .terms-conditions-block ul li:last-child {
            margin-bottom: 0;
        }

        .print-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background-color: #333;
        }

        @media print {
            body { padding: 0; }
            .invoice-container { 
                border: none;
                max-width: 100%; 
            }
            .print-button, .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width: 900px; margin: 0 auto 15px auto;">
    <a href="{{ url()->previous() }}" style="display: inline-block; padding: 8px 16px; background-color: #000; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
        &larr; Back
    </a>
</div>

<div class="invoice-container">
    <div class="invoice-header">
        <div class="company-logo">
            @if($appSettings['company_logo'])
                <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="{{ $appSettings['company_name'] }}" style="max-width: 180px; margin-bottom: 5px;">
            @endif
            <div style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px;">
                @if(!empty($appSettings['company_social']))
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:14px;height:14px;fill:#000;"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg>
                    <a href="{{ strpos($appSettings['company_social'], 'http') === 0 ? $appSettings['company_social'] : 'https://'.$appSettings['company_social'] }}" target="_blank" style="font-size: 13px; font-weight: 500; color: #000; text-decoration: none;">{{ preg_replace('#^https?://#', '', rtrim($appSettings['company_social'], '/')) }}</a>
                </div>
                @endif
                @if(!empty($appSettings['company_phone']))
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:14px;height:14px;fill:#000;"><path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z"/></svg>
                    <span style="font-size: 13px; font-weight: 500; color: #000;">{{ $appSettings['company_phone'] }}</span>
                </div>
                @endif
                @if(!empty($appSettings['company_website']))
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:14px;height:14px;fill:#000;"><path d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64h185.4c2.2 20.4 3.3 41.8 3.3 64zm28.8-64h123.1c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 20.9 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>
                    <a href="{{ strpos($appSettings['company_website'], 'http') === 0 ? $appSettings['company_website'] : 'https://'.$appSettings['company_website'] }}" target="_blank" style="font-size: 13px; font-weight: 500; color: #000; text-decoration: none;">{{ preg_replace('#^https?://#', '', rtrim($appSettings['company_website'], '/')) }}</a>
                </div>
                @endif

            </div>
        </div>
        <div class="invoice-title" style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
            <h2 style="font-size: 38px; font-weight: 900; margin: 0 0 15px 0; letter-spacing: 0.5px; color: #000; line-height: 1;">
                @if(strtolower($sale->sale_type) === 'estimate')
                    ESTIMATE
                @elseif(strtolower($sale->sale_type) === 'booking')
                    BOOKING
                @else
                    SALE
                @endif
            </h2>
            <div style="display: flex; justify-content: flex-end; align-items: flex-start; gap: 30px;">
                @if(!empty($appSettings['secondary_logo']))
                <div style="text-align: center; display: flex; flex-direction: column; align-items: center;">
                    <img src="{{ asset('storage/' . $appSettings['secondary_logo']) }}" alt="Secondary Logo" style="max-height: 70px; object-fit: contain;">
                </div>
                @endif
                <div style="text-align: right; font-size: 13px; font-weight: 700; color: #000; line-height: 1.6; margin-top: 5px;">
                    <p style="margin: 0;">{{ \Carbon\Carbon::parse($sale->sale_date)->format('n/j/Y') }}</p>
                    <p style="margin: 0; white-space: nowrap;">REF #: {{ $sale->invoice_number }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="invoice-info-section">
        <div class="info-block">
            <div class="info-block-title">Bill To</div>
            <p><strong>{{ $party->business_name ?? $party->name }}</strong></p>
            <p>{{ !empty($party->address) ? $party->address : 'Address Not Provided' }}</p>
            <p>Phone: {{ $party->phone ?: 'N/A' }}</p>
        </div>
        
        <div class="info-block">
            <div class="info-block-title">Invoice Details</div>
            <p><strong>Invoice No:</strong> #{{ $sale->invoice_number }}</p>
            <p><strong>Invoice Date:</strong> {{ date('d-M-Y', strtotime($sale->sale_date)) }}</p>
            @if(strtolower($sale->sale_type) !== 'estimate')
            <p><strong>Delivery Date:</strong> {{ !empty($sale->delivery_date) ? date('d-M-Y', strtotime($sale->delivery_date)) : 'Not Scheduled' }}</p>
            @endif
        </div>
    </div>

    <div style="padding: 0 30px;">
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 5%;">#</th>
                    <th style="width: 45%;">Product Name</th>
                    <th style="text-align: center; width: 10%;">Quantity</th>
                    <th style="text-align: center; width: 10%;">Unit</th>
                    <th style="text-align: right; width: 15%;">Price/unit</th>
                    <th style="text-align: right; width: 15%;">amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = json_decode($sale->item) ?? [];
                    $qtys = json_decode($sale->qty) ?? [];
                    $units = json_decode($sale->unit) ?? [];
                    $rates = json_decode($sale->rate) ?? [];
                    $amounts = json_decode($sale->amount) ?? [];
                @endphp
                @foreach($items as $i => $item)
                    @if(!empty($item))
                    <tr>
                        <td style="text-align: center;">{{ $loop->iteration }}</td>
                        <td><strong>{{ $item }}</strong></td>
                        <td style="text-align: center;">{{ ($qtys[$i] ?? 0) == 0 ? '-' : $qtys[$i] }}</td>
                        <td style="text-align: center;">{{ empty($units[$i]) ? '-' : strtoupper($units[$i]) }}</td>
                        <td style="text-align: right;">{{ number_format((float)($rates[$i] ?? 0), 2) }}</td>
                        <td style="text-align: right;"><strong>{{ number_format((float)($amounts[$i] ?? 0), 2) }}</strong></td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-section {{ strtolower($sale->sale_type) === 'estimate' ? 'estimate-summary' : '' }}">
        @if(strtolower($sale->sale_type) !== 'estimate')
        <div class="ledger-section">
            <div class="ledger-title">Account Statement</div>
            <div class="ledger-row">
                <span>{{ $ledger_info->label_prev }}</span>
                <span>RS {{ number_format($ledger_info->previous_balance, 2) }}</span>
            </div>
            <div class="ledger-row">
                <span>Current Invoice Amount</span>
                <span>{{ $ledger_info->operator }} {{ number_format($sale->remaining_amount, 2) }}</span>
            </div>
            <div class="ledger-row">
                <span>{{ $ledger_info->label_curr }} (CLOSING)</span>
                <span>RS {{ number_format($ledger_info->current_balance, 2) }}</span>
            </div>
            <p class="terms-note">* Goods once sold will not be returned. Payment terms apply.</p>
        </div>
        @else
        <div style="display: none;"></div>
        @endif

        <div class="totals-box">
            <div class="total-row">
                <span>Sub Total</span>
                <span>{{ number_format($sale->grand_total, 2) }}</span>
            </div>
            @if($sale->discount_value > 0)
            <div class="total-row">
                <span>Discount</span>
                <span>- {{ number_format($sale->discount_value, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>Net Total</span>
                <span>RS {{ number_format($sale->net_amount, 2) }}</span>
            </div>
            @if(strtolower($sale->sale_type) !== 'estimate')
            <div class="total-row">
                <span>{{ $sale->party_type === 'walkin' ? 'Amount Paid' : 'Advance Paid' }}</span>
                <span>{{ number_format($sale->advance_amount, 2) }}</span>
            </div>
            <div class="total-row balance-due">
                <span>Balance Due</span>
                <span>RS {{ number_format($sale->remaining_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($appSettings['invoice_terms']))
    <div class="terms-conditions-block">
        <h4>Terms &amp; Conditions</h4>
        <ul>
            @foreach(explode("\n", str_replace("\r", "", $appSettings['invoice_terms'])) as $term)
                @if(trim($term) !== '')
                    <li>{{ $term }}</li>
                @endif
            @endforeach
        </ul>
    </div>
    @endif

    <div style="margin-left: 30px; margin-top: 15px; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;">
        @if(!empty($appSettings['company_address']))
        <div style="display: flex; align-items: flex-start; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:20px;height:20px;fill:#000;"><path d="M256 0c-88.37 0-160 71.63-160 160 0 102.34 140.09 335.79 150.93 346.52 4.79 5.37 13.35 5.37 18.14 0C275.91 495.79 416 262.34 416 160c0-88.37-71.63-160-160-160zm0 240c-44.18 0-80-35.82-80-80s35.82-80 80-80 80 35.82 80 80-35.82 80-80 80z"/></svg>
            <span style="font-size: 11px; font-weight: 700; color: #000; padding-top: 3px;">{!! nl2br(e($appSettings['company_address'])) !!}</span>
        </div>
        @endif
        @if(!empty($appSettings['company_address_2']))
        <div style="display: flex; align-items: flex-start; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="width:20px;height:20px;fill:#000;"><path d="M256 0c-88.37 0-160 71.63-160 160 0 102.34 140.09 335.79 150.93 346.52 4.79 5.37 13.35 5.37 18.14 0C275.91 495.79 416 262.34 416 160c0-88.37-71.63-160-160-160zm0 240c-44.18 0-80-35.82-80-80s35.82-80 80-80 80 35.82 80 80-35.82 80-80 80z"/></svg>
            <span style="font-size: 11px; font-weight: 700; color: #000; padding-top: 3px;">{!! nl2br(e($appSettings['company_address_2'])) !!}</span>
        </div>
        @endif
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Customer Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Authorized Signature</div>
        </div>
    </div>

    <div class="invoice-footer">
        <p>Powered by ProWave Software Solutions | Contact: 0317-3836223</p>
    </div>
</div>

<button class="print-button" onclick="window.print()">Print Invoice</button>

</body>
</html>
