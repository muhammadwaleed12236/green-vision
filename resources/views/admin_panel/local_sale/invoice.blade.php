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
            align-items: center;
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
            <p>{{ $appSettings['company_phone'] }}</p>
        </div>
        <div class="invoice-title">
            <h2>
                @if(strtolower($sale->sale_type) === 'estimate')
                    ESTIMATE RECEIPT
                @elseif(strtolower($sale->sale_type) === 'booking')
                    BOOKING RECEIPT
                @else
                    SALE RECEIPT
                @endif
            </h2>
            <p>Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y') }}</p>
            <p>Time: {{ \Carbon\Carbon::parse($sale->sale_date)->format('h:i A') }}</p>
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

    <div class="summary-section">
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
            <div class="total-row">
                <span>{{ $sale->party_type === 'walkin' ? 'Amount Paid' : 'Advance Paid' }}</span>
                <span>{{ number_format($sale->advance_amount, 2) }}</span>
            </div>
            <div class="total-row balance-due">
                <span>Balance Due</span>
                <span>RS {{ number_format($sale->remaining_amount, 2) }}</span>
            </div>
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
